<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Habilidad;
use App\Models\Horario;
use App\Models\Lugar;
use App\Models\Servicio;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ServicioController extends Controller
{
    public function listado()
    {
        $titulo = "ETT - Listado Servicios";

        $lugares = Lugar::whereGestorId(\Auth::user()->id)->get();
        $habilidades = Habilidad::orderBy('nombre')->get();

        return view('gestor.servicios.listado', compact([
            'titulo',
            'lugares',
            'habilidades',
        ]));
    }

    public function nuevo()
    {
        $titulo = "ETT - Nuevo Servicio";

        $lugares = Lugar::whereGestorId(\Auth::user()->id)->get();
        $habilidades = Habilidad::orderBy('nombre')->get();

        return view('gestor.servicios.nuevo', compact([
            'titulo',
            'lugares',
            'habilidades',
        ]));
    }

    public function listadoDatatable(Request $request)
    {
        $filtros = $request->get('filtros');
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        //$search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        //$searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = Servicio::filtroServiciosGestor(true,false,false);
        $totalRecordswithFilter = Servicio::filtroServiciosGestor(false,true,false, $filtros);

        // Fetch records
        $records = Servicio::filtroServiciosGestor(false,false,true, $filtros, $start, $rowperpage, $columnName, $columnSortOrder);

        $data_arr = array();

        foreach($records as $record){
            $id = $record->id;
            $nombre = $record->nombre;
            $lugar = $record->nombre_lugar;
            $estado = $record->estado;
            $fecha = Carbon::parse($record->fecha)->format('d/m/y H:i');

            $data_arr[] = array(
                "id" => $id,
                "nombre" => $nombre,
                "lugar" => $lugar,
                "estado" => $estado,
                "fecha" => $fecha,
                "acciones" => null,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data_arr
        );

        echo json_encode($response);
    }

    public function nuevoServicioAjax(Request $request)
    {
        $exito = true;
        $mensaje = 'Servicio creado correctamente';
        $usuario = auth()->user();
        $status = Response::HTTP_OK;
        $mensajePersonalizado = '';
        $errorCode = 0;

        $servicio=null;

        try
        {
            DB::beginTransaction();

            $datosServicio = $request->datos;
            if(empty($datosServicio))
            {
                throw new Exception('No se han enviado datos', 100);
            }
            elseif(empty($datosServicio["nombre"]))
            {
                throw new Exception('El nombre es obligatorio', 100);
            }
            elseif(empty($datosServicio["lugar_id"]))
            {
                throw new Exception('El lugar es obligatorio', 100);
            }
            elseif(empty($datosServicio["habilidades"]))
            {
                throw new Exception('Las habilidades son obligatorias', 100);
            }

            $habilidades = $datosServicio["habilidades"];
            $horarios = $datosServicio["horarios"];

            //Eliminamos del array de datos las habilidades y los horarios
            unset($datosServicio["habilidades"]);
            unset($datosServicio["horarios"]);

            //Añadimos el gestor al servicio
            $datosServicio["gestor_id"] = auth()->user()->id;
            //Añadimos el estado del servicio por defecto a "buscando"
            $datosServicio["estado"] = "buscando";

            //Creamos el servicio
            $servicio = Servicio::create($datosServicio);

            //Reemplaza todas las habilidades en el servicio
            $servicio->habilidades()->sync($habilidades);

            //Creamos los horarios
            foreach($horarios as $horario)
            {
                $fechaInicio = Carbon::parse($horario["fecha_inicio"]);
                $fechaFin = Carbon::parse($horario["fecha_fin"]);
                $horaInicio = $horario["hora_inicio"];
                $horaFin = $horario["hora_fin"];
                $diferenciaDias = $fechaInicio->diffInDays($fechaFin);

                $fecha = $fechaInicio;
                for($i=0; $i<=$diferenciaDias; $i++)
                {
                    Horario::create([
                        'servicio_id' => $servicio->id,
                        'fecha_inicio' => $fecha->format('Y-m-d') . " " . $horaInicio,
                        'fecha_fin' => $fecha->format('Y-m-d') . " " . $horaFin,
                        'numero_trabajadores' => $horario["numero_trabajadores"],
                    ]);
                    $fecha = $fecha->addDay();
                }
            }

            DB::commit();
        }
        catch(Throwable $e)
        {
            $exito = false;
            $mensaje = 'Error al crear el servicio';
            $status = Response::HTTP_BAD_REQUEST;
            $mensajePersonalizado = $e->getMessage();
            $errorCode = $e->getCode();

            Log::error("\nUsuario: ". $usuario->id. " \nMensaje: ". $mensajePersonalizado);
            Log::error($e->getTraceAsString());

            DB::rollBack();
        }

        return response()->json([
            'exito' => $exito,
            'mensaje' => $mensaje,
            'mensajePersonalizado' => $mensajePersonalizado,
            'errorCode' => $errorCode,
            'servicio' => $servicio
        ],$status);
    }

    public function obtenerServicioAjax(Request $request)
    {
        $exito = true;
        $mensaje = 'Servicio obtenido correctamente';
        $usuario = auth()->user();
        $status = Response::HTTP_OK;
        $mensajePersonalizado = '';
        $errorCode = 0;

        $servicio=null;
        $habilidades=[];
        $horarios=[];

        try
        {
            if(empty($request->servicio_id))
            {
                throw new Exception('No se ha pasado el id del servicio', 100);
            }

            $servicio = Servicio::select([
                's.*',
                'l.nombre as nombre_lugar'
            ])
                ->from('servicios as s')
                ->join('lugares as l', 's.lugar_id', '=', 'l.id')
                ->where('s.id', '=', $request->servicio_id)
                ->first();

            $habilidades = $servicio->habilidades->pluck('id')->toArray();
            $horarios = $servicio->horarios;

            if(!$servicio)
            {
                throw new Exception('No existe el servicio', 100);
            }
        }
        catch(Throwable $e)
        {
            $exito = false;
            $mensaje = 'Error al obtener el servicio';
            $status = Response::HTTP_BAD_REQUEST;
            $mensajePersonalizado = $e->getMessage();
            $errorCode = $e->getCode();

            Log::error("\nUsuario: ". $usuario->id. " \nMensaje: ". $mensajePersonalizado);
            Log::error($e->getTraceAsString());
        }

        return response()->json([
            'exito' => $exito,
            'mensaje' => $mensaje,
            'mensajePersonalizado' => $mensajePersonalizado,
            'errorCode' => $errorCode,
            'servicio' => $servicio,
            'habilidades' => $habilidades,
            'horarios' => $horarios,
        ],$status);
    }

    public function editarServicioAjax(Request $request)
    {
        $exito = true;
        $mensaje = 'Servicio actualizado correctamente';
        $usuario = auth()->user();
        $status = Response::HTTP_OK;
        $mensajePersonalizado = '';
        $errorCode = 0;

        $servicio=null;

        try
        {
            DB::beginTransaction();

            $datosServicio = $request->datos;
            if(empty($datosServicio))
            {
                throw new Exception('No se han enviado datos', 100);
            }

            if(empty($datosServicio["servicio_id"]))
            {
                throw new Exception('No se ha pasado el id del servicio', 100);
            }

            $servicio = Servicio::find($datosServicio["servicio_id"]);
            if(!$servicio)
            {
                throw new Exception('No existe el servicio', 100);
            }

            $habilidades = $datosServicio["habilidades"];
            $horarios = $datosServicio["horarios"];

            //Eliminamos del array de datos las habilidades y los horarios
            unset($datosServicio["habilidades"]);
            unset($datosServicio["horarios"]);

            //Reemplaza todas las habilidades en el servicio
            $servicio->habilidades()->sync($habilidades);

            //BORRADO DE HORARIOS QUE YA NO EXISTEN
            //Obtenemos el ID de los horarios del servicio y el ID de los horarios nuevos recibidos
            $horariosAntiguos = $servicio->horarios->pluck('id')->toArray();
            $horariosNuevos = array_column($horarios, 'horario_id');
            //Nos quedamos con el ID de los horarios diferentes (los que se han borrado)
            $horariosABorrar = array_diff($horariosAntiguos, $horariosNuevos);
            //Borramos los horarios que ya no sirven
            foreach($horariosABorrar as $horarioABorrar)
            {
                Horario::find($horarioABorrar)->delete();
            }

            //Modificamos los horarios existentes y creamos los nuevos
            foreach($horarios as $horario)
            {
                $horarioId = $horario["horario_id"];
                $fechaInicio = Carbon::parse($horario["fecha_inicio"]);
                $fechaFin = Carbon::parse($horario["fecha_fin"]);
                $horaInicio = $horario["hora_inicio"];
                $horaFin = $horario["hora_fin"];

                //Si existe lo actualizamos
                if(Horario::find($horarioId))
                {
                    Horario::where('id','=', $horarioId)
                        ->update([
                            'servicio_id' => $servicio->id,
                            'fecha_inicio' => $fechaInicio->format('Y-m-d') . " " . $horaInicio,
                            'fecha_fin' => $fechaFin->format('Y-m-d') . " " . $horaFin,
                            'numero_trabajadores' => $horario["numero_trabajadores"],
                        ]);
                }
                else //Si no existe lo creamos
                {
                    Horario::create([
                        'servicio_id' => $servicio->id,
                        'fecha_inicio' => $fechaInicio->format('Y-m-d') . " " . $horaInicio,
                        'fecha_fin' => $fechaFin->format('Y-m-d') . " " . $horaFin,
                        'numero_trabajadores' => $horario["numero_trabajadores"],
                    ]);
                }

            }

            //Actualizamos los datos del servicio
            $servicio->update($datosServicio);

            DB::commit();
        }
        catch(Throwable $e)
        {
            $exito = false;
            $mensaje = 'Error al actualizar el servicio';
            $status = Response::HTTP_BAD_REQUEST;
            $mensajePersonalizado = $e->getMessage();
            $errorCode = $e->getCode();

            Log::error("\nUsuario: ". $usuario->id. " \nMensaje: ". $mensajePersonalizado);
            Log::error($e->getTraceAsString());

            DB::rollBack();
        }

        return response()->json([
            'exito' => $exito,
            'mensaje' => $mensaje,
            'mensajePersonalizado' => $mensajePersonalizado,
            'errorCode' => $errorCode,
            'servicio' => $servicio
        ],$status);
    }
}
