@extends('adminlte::page')

@section('title', 'ReciclaUSAT')

{{-- @section('content_header')
  <h1>Marcas</h1>
@stop --}}

@section('content')
    <div class="p-2"></div>
    <div class="card">
        <div class="card">
            <div class="card-header">
                <h3>Actividad</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-4">
                        <label for="">Actividad:</label>
                        {{ $activity->name }}
                    </div>
                    <div class="form-group col-4">
                        <label for="">Fecha Inicio:</label>
                        {{ $activity->date_start }}
                    </div>
                    <div class="form-group col-4">
                        <label for="">Fecha Fin:</label>
                        {{ $activity->date_end }}
                    </div>
                </div>
            </div>

        </div>

    <div class="card">
        <div class="card-header">
            <button class="btn btn-success float-right" id="btnNuevo" data-id={{ $activity->id }}><i class="fas fa-plus"></i> Nuevo</button>
            <h3>HORARIOS</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped" id="datatable">
                <thead>
                    <tr>
                        <th>DIA</th>
                        <th>VEHICULO</th>
                        <th>CONDUCTOR</th>
                        <th>TIPO</th>
                        <th>INCIO</th>
                        <th>FIN</th>
                        <th width="10">EDITAR</th>
                        <th width="10">ACTIVIDAD</th>
                        <th width="10">ELIMINAR</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('admin.activities.index') }}" class="btn btn-danger float-right"><i class="fas fa-chevron-left"></i> Retornar</a>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulario de la modelo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="formModaldate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulario de la modelo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop
@section('js')
    <script>
        $(document).ready(function() {
            var table = $('#datatable').DataTable({
                "ajax": "{{ route('admin.activities.show', $activity->id) }}", // La ruta que llama al controlador vía AJAX
                "columns": [
                    {
                        "data": "day",
                    },
                    {
                        "data": "vehicle",
                    },
                    {
                        "data": "driver",
                    },
                    {
                        "data": "type",
                    },
                    {
                        "data": "time_start",
                    },
                    {
                        "data": "time_end",
                    },
                    {
                        "data": "edit",
                        "orderable": false,
                        "searchable": false,
                    }
                    ,
                    {
                        "data": "activity",
                        "orderable": false,
                        "searchable": false,
                    }
                    ,
                    {
                        "data": "delete",
                        "orderable": false,
                        "searchable": false,
                    }
                    /*{
                        "data": "edit",
                        "orderable": false,
                        "searchable": false,
                    },
                    {
                        "data": "delete",
                        "orderable": false,
                        "searchable": false,
                    }*/

                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });
        });

        $('#btnNuevo').click(function() {
            var id = $(this).attr('data-id');
            $.ajax({
                url: "{{ route('admin.activityschedules.register', '_id') }}".replace('_id', id),
                type: "GET",
                success: function(response) {
                    $("#formModal #exampleModalLabel").html("Nuevo Horario");
                    $("#formModal .modal-body").html(response);
                    $("#formModal").modal("show");

                    $("#formModal form").on("submit", function(e) {
                        e.preventDefault();

                        var form = $(this);
                        var formData = new FormData(this);

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $("#formModal").modal("hide");
                                refreshTable();
                                Swal.fire('Proceso existoso', response.message,
                                    'success');
                            },
                            error: function(xhr) {
                                var response = xhr.responseJSON;
                                Swal.fire('Error', response.message, 'error');
                            }
                        })

                    })

                }
            });
        });


        $(document).on('click', '.btnActivity', function() {
            var id = $(this).attr("id");

            $.ajax({
                url: "{{ route('admin.scheduledates.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $("#formModaldate #exampleModalLabel").html("Nuevo Registro");
                    $("#formModaldate .modal-body").html(response);
                    $("#formModaldate").modal("show");


                    $("#formModaldate form").on("submit", function(e) {
                        e.preventDefault();

                        var form = $(this);
                        var formData = new FormData(this);

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $("#formModaldate").modal("hide");
                                refreshTable();
                                Swal.fire('Proceso existoso', response.message,
                                    'success');
                            },
                            error: function(xhr) {
                                var response = xhr.responseJSON;
                                Swal.fire('Error', response.message, 'error');
                            }
                        })

                    })
                }
            });


        })



        $(document).on('click', '.btnEditar', function() {
            var id = $(this).attr("id");

            $.ajax({
                url: "{{ route('admin.activityschedules.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $("#formModal #exampleModalLabel").html("Modificar Horario");
                    $("#formModal .modal-body").html(response);
                    $("#formModal").modal("show");

                    $("#formModal form").on("submit", function(e) {
                        e.preventDefault();

                        var form = $(this);
                        var formData = new FormData(this);

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $("#formModal").modal("hide");
                                refreshTable();
                                Swal.fire('Proceso existoso', response.message,
                                    'success');
                            },
                            error: function(xhr) {
                                var response = xhr.responseJSON;
                                Swal.fire('Error', response.message, 'error');
                            }
                        })

                    })
                }
            });


        })

        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: "Está seguro de eliminar?",
                text: "Está acción no se puede revertir!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, eliminar!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function(response) {
                            refreshTable();
                            Swal.fire('Proceso existoso', response.message, 'success');
                        },
                        error: function(xhr) {
                            var response = xhr.responseJSON;
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        });

        function refreshTable() {
            var table = $('#datatable').DataTable();
            table.ajax.reload(null, false); // Recargar datos sin perder la paginación
        }
    </script>
@endsection
