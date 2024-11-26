@extends('adminlte::page')

@section('title', 'ReciclaUSAT')

@section('content')
    <div class="p-2"></div>
    <div class="card">
        <div class="card-header">
            <button class="btn btn-success float-right" id="btnNuevo" data-id={{ $zone->id }}><i class="fas fa-plus"></i>
                Agregar</button>


            <h3>Perímetro de la Zona</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <label for="">Zona:</label>
                            <p>{{ $zone->name }}</p>
                            <label for="">Sector:</label>
                            <p>{{ $zone->sector }}</p>
                            <label for="">Área:</label>
                            <p>{{ $zone->area }} metros</p>
                            <label for="">Descripción:</label>
                            <p>{{ $zone->description }}</p>
                        </div>

                    </div>
                </div>
                <div class="col-8">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>LATITUD</th>
                                        <th>LONGITUD</th>
                                        <th></th>
                                    </tr>

                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.zones.index') }}" class="btn btn-danger float-right"><i class="fas fa-chevron-left"></i> Retornar</a>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Coordenadas</h5>
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

@section('js')
    <script>
        /* $("#btnNuevo").click(function() {
                                                        var id = $(this).attr('data-id');
                                                        $.ajax({
                                                            url: "{{ route('admin.zonecoords.edit', '_id') }}".replace('_id', id),
                                                            type: "GET",
                                                            success: function(response) {
                                                                $("#formModal .modal-body").html(response);
                                                                $("#formModal").modal("show");
                                                            }
                                                        })
                                                    })*/

        $(document).ready(function() {
            var table = $('#datatable').DataTable({
                "ajax": "{{ route('admin.zones.show', $zone->id) }}", // La ruta que llama al controlador vía AJAX
                "columns": [{
                        "data": "latitude",
                        "orderable": false,
                        "searchable": false,
                    },
                    {
                        "data": "longitude",
                        "orderable": false,
                        "searchable": false,
                    },
                    {
                        "data": "actions",
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
                url: "{{ route('admin.zonecoords.edit', '_id') }}".replace('_id', id),
                type: "GET",
                success: function(response) {
                    $("#formModal #exampleModalLabel").html("Agregar coordenada");
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



@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop
