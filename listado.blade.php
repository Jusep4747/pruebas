@extends('layouts.layout')

@section('styles')
    @parent
    <link href="{{mix('assets/css/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />

    <style>
        .nowrap{
            white-space: nowrap;
        }
        .icono-opciones{
            font-size: 1rem;
          }

        .modal-lg{
            max-width: 992px;
        }

        .cursor-disabled{
            cursor: not-allowed;
        }
    </style>
@endsection

@section('content')

    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header">
            <div class="card-title">
                <span class="card-icon">
                    <i class="fa fa-briefcase text-primary"></i>
                </span>
                <h3 class="card-label">Listado de servicios</h3>
            </div>
        </div>
        <div class="card-body">
            <!--begin: Search Form-->
            <form class="mb-15">
                <div class="row mb-6">
                    <div class="col-lg-4 mb-lg-0 mb-6">
                        <label for="buscadorNombre">Nombre:</label>
                        <input id="buscadorNombre" type="text" class="form-control datatable-input" placeholder="Hotel ABC" data-col-index="0" onkeyup="aplicarBusqueda()"/>
                    </div>
                    <div class="col-lg-3 mb-lg-0 mb-6">
                        <label for="buscadorLugar">Lugar:</label>
                        <select id="buscadorLugar" class="form-control select2" onchange="aplicarBusqueda()">
                            <option value=""></option>
                            @foreach($lugares as $lugar)
                                <option value="{{$lugar->id}}">{{$lugar->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 mb-lg-0 mb-6">
                        <label for="buscadorEstado">Estado:</label>
                        <select id="buscadorEstado" class="form-control select2" onchange="aplicarBusqueda()">
                            <option value=""></option>
                            <option value="buscando">Buscando candidatos</option>
                            <option value="espera">En espera</option>
                            <option value="en-curso">En curso</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                    <div class="col-lg-3 mb-lg-0 mb-6">
                        <label>Fecha:</label>
                        <div class="input-daterange input-group" id="kt_datepicker">
                            <input id="buscadorFechaDesde" type="text" class="form-control datatable-input" name="start" placeholder="Desde" data-col-index="3" onchange="aplicarBusqueda()"/>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-ellipsis-h"></i>
                                </span>
                            </div>
                            <input id="buscadorFechahasta" type="text" class="form-control datatable-input" name="end" placeholder="Hasta" data-col-index="4" onchange="aplicarBusqueda()"/>
                        </div>
                    </div>
                </div>

                <div class="row mt-8 justify-content-between">
                    <div class="col-lg-4">
                        <button class="btn btn-primary btn-primary--icon" id="kt_search">
                            <span>
                                <i class="la la-search"></i>
                                <span>Buscar</span>
                            </span>
                        </button>&#160;&#160;
                        <button class="btn btn-secondary btn-secondary--icon" id="kt_reset">
                            <span>
                                <i class="la la-close"></i>
                                <span>Limpiar</span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            <!--begin: Datatable-->
            <!--begin: Datatable-->
            <table class="table table-bordered table-hover table-checkable" id="kt_datatable">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Lugar</th>
                    <th>Estado</th>
                    <th>Creacion</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>Nombre</th>
                    <th>Lugar</th>
                    <th>Estado</th>
                    <th>Creacion</th>
                    <th>Acciones</th>
                </tr>
                </tfoot>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->

    <!-- Modal-->
    <div class="modal fade" id="modalEditarServicio" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar lugar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input id="modalEditarServicioId" type="hidden">
                    <div class="row">
                        <div class="col-lg-6 mb-lg-0 mb-6">
                            <label for="modalEditarServicioNombre">Nombre:</label>
                            <div class="form-group">
                                <input id="modalEditarServicioNombre" type="text" class="form-control" placeholder="Catering en restaurante">
                            </div>
                        </div>
                        <div class="col-lg-6 mb-lg-0 mb-6">
                            <label for="modalEditarServicioLugar">Lugar:</label>
                            <select id="modalEditarServicioLugar" class="form-control select2" disabled>
                                @foreach($lugares as $lugar)
                                    <option value="{{$lugar->id}}">{{$lugar->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-lg-0 mb-6">
                            <label for="modalEditarServicioDescripcion">Descripción:</label>
                            <div class="form-group">
                                <textarea id="modalEditarServicioDescripcion" rows="2" class="form-control" placeholder="Descripción del servicio a realizar"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col mb-lg-0 mb-6">
                            <label for="modalEditarServicioHabilidades">Habilidades:</label>
                            <select id="modalEditarServicioHabilidades" class="form-control select2" multiple>
                                @foreach($habilidades as $habilidad)
                                    <option value="{{$habilidad->id}}">{{$habilidad->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="mt-6">

                    <h5>Horario</h5>
                    <div id="kt_repeater_1" class="pt-2">
                        <div data-repeater-list="">
                            <div class="row horario" data-repeater-item>

                                <input type="hidden" name="horario-id" class="horario-id">

                                <div class="col-lg-3 mb-lg-0 mb-6">
                                    <label>Fecha:</label>
                                    <div class="form-group">
                                        <input type="text" name="fecha-servicio" class="form-control fecha-servicio" placeholder="Fecha servicio">
                                    </div>
                                </div>
                                <div class="col-lg-2 mb-lg-0 mb-6">
                                    <label>Hora inicio:</label>
                                    <div class="form-group">
                                        <input type="text" name="hora-inicio-servicio" class="form-control hora-inicio-servicio" placeholder="Hora inicio">
                                    </div>
                                </div>
                                <div class="col-lg-2 mb-lg-0 mb-6">
                                    <label>Hora fin:</label>
                                    <div class="form-group">
                                        <input type="text" name="hora-fin-servicio" class="form-control hora-fin-servicio" placeholder="Hora fin">
                                    </div>
                                </div>
                                <div class="col-lg-3 mb-lg-0 mb-6">
                                    <label>Trabajadores:</label>
                                    <div class="form-group">
                                        <input type="text" name="trabajadores-servicio" class="form-control trabajadores-servicio text-center" placeholder="Trabajadores" value="1">
                                    </div>
                                </div>
                                <div class="col-lg-2 text-center">
                                    <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger" style="position: relative; top: 25px;">
                                        <i class="la la-trash-o"></i>Eliminar
                                    </a>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                    <i class="la la-plus"></i>Añadir
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary font-weight-bold" onclick="actualizarDatosServicio()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script src="{{mix('assets/js/datatables.bundle.js')}}"></script>
    <script src="{{secure_asset('assets/js/select2-es.js') . config('app.version_cache')}}"></script>
    <script src="{{mix('assets/js/moment-with-locales.js')}}"></script>
    <script src="{{mix('assets/js/daterangepicker-es.js')}}"></script>

    <script>
        let table = null;
        let repeater = null;

        $(document).ready(function(){

            $('#buscadorEstado').select2({
                placeholder: "Elige una opción",
                allowClear: true
            });

            $('#buscadorLugar').select2({
                placeholder: "Elige un lugar",
                allowClear: true
            });

            $('#modalEditarServicioLugar').select2({
                placeholder: "Selecciona el lugar",
                allowClear: false,
                width: '100%'
            });

            $('#modalEditarServicioHabilidades').select2({
                placeholder: "Selecciona las habilidades",
                allowClear: false,
                width: '100%'
            });

            $('#modalEditarServicio').on('hidden.bs.modal', function (event) {
                limpiarFormularioEditarServicio();
            });
            $('#modalEditarServicio').on('shown.bs.modal', function (e) {
                autosize.update($("#modalEditarServicioDireccion"));
            });

            autosize($("#modalEditarServicioDireccion"));

            //Repetir elementos
            repeater = $('#kt_repeater_1').repeater({
                initEmpty: false,

                defaultValues: {
                    //'prueba': 'foo'
                },

                show: function () {
                    $(this).slideDown();

                    //FECHA SERVICIO
                    $(this).find('.fecha-servicio').daterangepicker({
                        buttonClasses: ' btn',
                        applyClass: 'btn-primary',
                        cancelClass: 'btn-secondary',
                        locale: languageDaterangepicker,
                    });

                    //HORA INICIO - FIN SERVICIO
                    $(this).find('.hora-inicio-servicio, .hora-fin-servicio').timepicker({
                        minuteStep: 5,
                        defaultTime: '',
                        showSeconds: false,
                        showMeridian: false,
                        snapToStep: true
                    });


                    //NUMERO TRABAJADORES SERVICIO
                    $(this).find('.trabajadores-servicio').TouchSpin({
                        buttondown_class: 'btn btn-secondary',
                        buttonup_class: 'btn btn-secondary',

                        min: 1,
                        max: 100,
                        step: 1,
                        decimals: 0,
                        boostat: 50,
                        maxboostedstep: 10,
                    });
                },

                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);
                },

                isFirstItemUndeletable: true
            });

            $.fn.dataTable.Api.register('column().title()', function() {
                return $(this.header()).text().trim();
            });

            table = $('#kt_datatable').DataTable({
                responsive: true,
                // Pagination settings
                dom: `<'row'<'col-sm-12'tr>>
			<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 dataTables_pager'lp>>`,
                // read more: https://datatables.net/examples/basic_init/dom.html

                lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "Todos"]],

                pageLength: 10,

                language: {
                    "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
                    "sInfo": "Página _PAGE_ de _PAGES_",
                    "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    "sSearchPlaceholder": "Buscar...",
                    "sLengthMenu": "Resultados :  _MENU_",
                    "sEmptyTable": "No existen servicios",
                    "sZeroRecords": "No hay servicios",
                    "sInfoEmpty": "Mostrando 0 de 0 servicios",
                    //"info": "Mostrando de _START_ a _END_ de _TOTAL_ radios",
                    "sInfoFiltered": "(filtrado de _MAX_ servicios)",
                    "processing": "Cargando..."
                },
                order: [[ 3, "desc" ]],
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{route('gestor.servicios.listado.datatable')}}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: function(data){
                        // parameters for custom backend script demo
                        data.columnsDef= [
                            'nombre',
                            'lugar',
                            'estado',
                            'fecha',
                            'acciones'
                        ];

                        let filtros = {
                            nombre : $("#buscadorNombre").val().trim(),
                            lugar_id : $("#buscadorLugar option:selected").val(),
                            estado : $("#buscadorProvincia option:selected").val(),
                            fecha_desde : $("#buscadorFechaDesde").val(),
                            fecha_hasta : $("#buscadorFechahasta").val()
                        };
                        data.filtros = filtros;
                    },
                },
                createdRow: function( row, data, dataIndex ) {
                    $(row).attr('id', 'servicio'+data.id);
                },
                columns: [
                    {data: 'nombre'},
                    {data: 'lugar'},
                    {data: 'estado'},
                    {data: 'fecha', className: 'text-center nowrap'},
                    {data: 'acciones', className: 'text-center nowrap', responsivePriority: -1},
                ],

                columnDefs: [
                    {
                        targets: -1,
                        title: 'Acciones',
                        orderable: false,
                        render: function(data, type, full, meta) {

                            let acciones =
                                '<div class="dropdown dropdown-inline">' +
                                    '<a href="javascript:;" class="btn btn-sm btn-clean btn-icon" data-toggle="dropdown">' +
                                        '<i class="la la-cog"></i>' +
                                    '</a>' +
                                    '<div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">' +
                                        '<ul class="nav nav-hoverable flex-column">' +
                                            '<li class="nav-item"><a class="nav-link" onclick="cancelarServicio('+full.id+', \'desactivar\', \''+full.nombre+'\', event)" href="#"><i class="nav-icon fa fa-ban icono-opciones"></i><span class="nav-text">Cancelar servicio</span></a></li>' +
                                        '</ul>' +
                                    '</div>' +
                                '</div>' +
                                '<a href="javascript:;" target="_blank"  class="btn btn-sm btn-clean btn-icon" title="Editar" onclick="modalEditarServicio('+full.id+', event)">' +
                                    '<i class="la la-edit"></i>' +
                                '</a>';

                            return acciones;
                        },
                    },
                    {
                        targets: 2,
                        render: function(data, type, full, meta) {
                            let status = {
                                'buscando': {'title': 'Buscando', 'class': ' label-light-primary'},
                                'espera': {'title': 'En espera', 'class': ' label-light-warning'},
                                'en-curso': {'title': 'En curso', 'class': ' label-light-info'},
                                'completado': {'title': 'Completado', 'class': ' label-light-success'},
                            };
                            if (typeof status[data] === 'undefined') {
                                return data;
                            }

                            return '<span class="label label-lg font-weight-bold' + status[data].class + ' label-inline">' + status[data].title + '</span>';
                        },
                        createdCell:  function (td, cellData, rowData, row, col) {
                            $(td).addClass('estado');
                        }
                    },
                ],
            });

            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.table().draw();
            });

            $('#kt_reset').on('click', function(e) {
                e.preventDefault();
                $('.datatable-input').each(function() {
                    $(this).val(''); //Limpiamos los campos
                    $(this).trigger('change'); //Actualizamos para los select2
                    table.column($(this).data('col-index')).search('', false, false);
                });
                table.table().draw();
            });

            $('#kt_datepicker').datepicker({
                todayHighlight: true,
                language: "es",
                format: 'yyyy-mm-dd',
                templates: {
                    leftArrow: '<i class="la la-angle-left"></i>',
                    rightArrow: '<i class="la la-angle-right"></i>',
                },
            });
        });

        function aplicarBusqueda(desdeInicio=true)
        {
            table.table().draw(desdeInicio);
        }

        function cancelarServicio(servicio_id, nombre, event)
        {
            event.preventDefault();

            let mensajeConfirmacion = (activarDesactivar === 'activar') ? 'Sí, ¡actívalo!' : 'Sí, ¡desactívalo!';
            Swal.fire({
                heightAuto: false,
                title: "¿Estás seguro? Esta opción es irreversible",
                text: "Vas a cancelar el servicio \""+nombre+"\".",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: mensajeConfirmacion,
                cancelButtonText: "Cancelar"
            }).then(function(result) {
                if (result.value) {

                    let datos = {
                        'servicio_id': servicio_id,
                    };
                    $.ajax({
                        url: '{{route('admin.lugares.activardesactivar.ajax')}}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'post',
                        data: datos,
                        success: function (data) {
                            if(data.exito)
                            {
                                aplicarBusqueda(false);

                                Swal.fire(
                                    "¡Cancelado!",
                                    "El servicio ha sido cancelado.",
                                    "success"
                                );
                            }
                            else
                            {
                                Swal.fire(
                                    "¡Error!",
                                    "No se ha podido cancelar el servicio.",
                                    "error"
                                );
                            }
                        },
                        error: function () {
                            let mensaje = error.responseJSON.errorCode === 100 ? error.responseJSON.mensajePersonalizado : error.responseJSON.mensaje;
                            Swal.fire(
                                "¡Error!",
                                mensaje,
                                "error"
                            );
                        }
                    });

                }
            });
        }

        function modalEditarServicio(servicio_id, event)
        {
            event.preventDefault();
            limpiarFormularioEditarServicio();

            $.ajax({
                url: '{{route('gestor.servicios.obtener.ajax')}}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                data: { servicio_id: servicio_id },
                success: function (data) {
                    if(data.exito)
                    {
                        let servicio = data.servicio;
                        let habilidades = data.habilidades;
                        let horarios = data.horarios;

                        $("#modalEditarServicioId").val(servicio.id);

                        $("#modalEditarServicio .modal-title").html('Servicio: '+servicio.nombre);

                        $("#modalEditarServicioNombre").val(servicio.nombre);
                        $("#modalEditarServicioDescripcion").val(servicio.descripcion);

                        $("#modalEditarServicioLugar").val(servicio.lugar_id);
                        $("#modalEditarServicioLugar").trigger('change');

                        $("#modalEditarServicioHabilidades").val(habilidades);
                        $("#modalEditarServicioHabilidades").trigger('change');

                        let listaHorarios = [];
                        $.each(horarios, function( index, value ) {
                            let fechaInicio = value.fecha_inicio.split(' ');
                            let fechaFin = value.fecha_fin.split(' ');

                            listaHorarios.push(
                                {
                                    'horario-id': value.id,
                                    'fecha-servicio': moment(fechaInicio[0], "YYYY-MM-DD").format('DD/MM/YYYY')+' - '+moment(fechaFin[0], "YYYY-MM-DD").format('DD/MM/YYYY'),
                                    'hora-inicio-servicio': fechaInicio[1],
                                    'hora-fin-servicio': fechaFin[1],
                                    'trabajadores-servicio': value.numero_trabajadores,
                                }
                            );
                        });

                        repeater.setList(listaHorarios);

                        //Mostramos el modal
                        $("#modalEditarServicio").modal("show");
                    }
                    else
                    {
                        let mensaje = data.errorCode === 100 ? data.mensajePersonalizado : data.mensaje;
                        Swal.fire(
                            "¡Error!",
                            mensaje,
                            "error"
                        );
                    }
                },
                error: function (error) {
                    let mensaje = error.responseJSON.errorCode === 100 ? error.responseJSON.mensajePersonalizado : error.responseJSON.mensaje;
                    Swal.fire(
                        "¡Error!",
                        mensaje,
                        "error"
                    );
                }
            });

        }

        function actualizarDatosServicio()
        {
            let servicio_id = $("#modalEditarServicioId").val();

            if(servicio_id === '')
            {
                toastError('No se ha pasado el servicio');
                return false;
            }

            let horarios = [];
            $(".horario").each(function(){

                let fecha = $(this).find(".fecha-servicio").val().split('-');

                let fecha_inicio = fecha[0].trim();
                fecha_inicio = moment(fecha_inicio, "DD/MM/YYYY").format('YYYY-MM-DD');

                let fecha_fin = fecha[1].trim();
                fecha_fin = moment(fecha_fin, "DD/MM/YYYY").format('YYYY-MM-DD');

                let hora_inicio = $(this).find(".hora-inicio-servicio").val().trim();
                let hora_fin = $(this).find(".hora-fin-servicio").val().trim();

                horarios.push({
                    horario_id: $(this).find(".horario-id").val(),
                    fecha_inicio: fecha_inicio,
                    fecha_fin: fecha_fin,
                    hora_inicio: hora_inicio,
                    hora_fin: hora_fin,
                    numero_trabajadores: $(this).find(".trabajadores-servicio").val()
                });
            });

            let datos = {
                datos: {
                    servicio_id: servicio_id,
                    nombre: $("#modalEditarServicioNombre").val().trim(),
                    //lugar_id: $("#modalEditarServicioLugar option:selected").val(),
                    habilidades: $("#modalEditarServicioHabilidades").val(),
                    descripcion: $("#modalEditarServicioDescripcion").val().trim(),
                    horarios: horarios,
                }
            };

            $.ajax({
                url: '{{route('gestor.servicios.editar.ajax')}}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                data: datos,
                success: function (data) {
                    let mensaje = data.errorCode === 100 ? data.mensajePersonalizado : data.mensaje;
                    if(data.exito)
                    {
                        aplicarBusqueda(false);
                        Swal.fire(
                            "¡Servicio actualizado!",
                            mensaje,
                            "success"
                        );
                    }
                    else
                    {
                        Swal.fire(
                            "¡Error!",
                            mensaje,
                            "error"
                        );
                    }
                },
                error: function (error) {
                    let mensaje = error.responseJSON.errorCode === 100 ? error.responseJSON.mensajePersonalizado : error.responseJSON.mensaje;
                    Swal.fire(
                        "¡Error!",
                        mensaje,
                        "error"
                    );
                }
            });
        }

        function limpiarFormularioEditarServicio()
        {
            $("#modalEditarServicioId").val('');
            $("#modalEditarServicio .modal-title").html('Editar Lugar');

            $("#modalEditarServicioNombre").val('');

            $("#modalEditarServicioLugar").val('');
            $("#modalEditarServicioLugar").trigger('change');

            $("#modalEditarServicioDescripcion").val('');

            $("#modalEditarServicioHabilidades").val('');
            $("#modalEditarServicioHabilidades").trigger('change');
        }

        function toastError(mensaje)
        {
            let content = {
                message : mensaje
            };
            let notify = $.notify(content, {
                type: 'danger',
                allow_dismiss: true,
                newest_on_top: true,
                mouse_over:  true,
            });
        }

    </script>
    <script>
        !function(a){a.fn.datepicker.dates.es={days:["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"],daysShort:["Dom","Lun","Mar","Mié","Jue","Vie","Sáb"],daysMin:["Do","Lu","Ma","Mi","Ju","Vi","Sa"],months:["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],monthsShort:["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],today:"Hoy",monthsTitle:"Meses",clear:"Borrar",weekStart:1,format:"dd/mm/yyyy"}}(jQuery);
    </script>
@endsection
