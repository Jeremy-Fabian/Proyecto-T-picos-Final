@extends('adminlte::page')

@section('title', 'ReciclaUSAT')

{{-- @section('content_header')
  <h1>Marcas</h1>
@stop --}}

@section('content')
    <div class="p-2"></div>
    <div class="card">
        <div class="card-header">
            <button class="btn btn-success float-right" id="btnNuevo"><i class="fas fa-plus"></i> Nuevo</button>
            <h3>Personal</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped" id="datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NOMBRE</th>
                        <th>FOTO</th>
                        <th>DNI</th>
                        <th>DIRECCIÓN</th>
                        <th>EMAIL</th>
                        <th>TIPO</th>
                        <th>LICENCIA</th>
                        <th>ZONA</th>



                        <th width="10"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulario de personal</h5>
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
                "ajax": "{{ route('admin.users.index') }}", // La ruta que llama al controlador vía AJAX
                "columns": [{
                        "data": "id",
                    },
                    {
                        "data": "name",
                    },
                    {
                        "data": "image",
                    },
                    {
                        "data": "dni",
                    },
                    {
                        "data": "address",
                    },
                    {
                        "data": "email",
                    },
                    {
                        "data": "typename",
                    },
                    {
                        "data": "license",
                    },
                    {
                        "data": "zname",
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

            $.ajax({
                url: "{{ route('admin.users.create') }}",
                type: "GET",
                success: function(response) {
                    $("#formModal #exampleModalLabel").html("Nuevo Personal");
                    $("#formModal .modal-body").html(response);
                    $("#formModal").modal("show");
                    // Inicializar eventos para elementos dinámicos
                    //--------------------------------------------------------------
                    const userTypeField = document.getElementById('usertype_id');
                    //--------------------------------------------------------------

                    const licenseField = document.getElementById('licenseField');
                    const licenseInput = document.getElementById('license');

                    if (userTypeField && licenseField && licenseInput) {
                        userTypeField.addEventListener('change', function() {
                            const userType = this.options[this.selectedIndex].text
                                .toLowerCase();
                            if (userType === 'conductor') {
                                licenseField.style.display = 'block';
                                licenseInput.required = true; // Hacer obligatorio
                            } else {
                                licenseField.style.display = 'none';
                                licenseInput.required = false; // Quitar obligatoriedad
                            }
                        });
                    }
                    //----------------------------------------------------------------------------
                    // Inicializar eventos para elementos dinámicos
                    const zonaField = document.getElementById('zonaField');
                    const zonaInput = document.getElementById('zone_id');

                    if (userTypeField && zonaField && zonaInput) {
                        userTypeField.addEventListener('change', function() {
                            const userType = this.options[this.selectedIndex].text
                                .toLowerCase();
                            if (userType === 'ciudadano') {
                                zonaField.style.display = 'block';
                                zonaInput.required = true; // Hacer obligatorio
                            } else {
                                zonaField.style.display = 'none';
                                zonaInput.required = false; // Quitar obligatoriedad
                            }
                        });
                    }
                    //----------------------------------------------------------------------------

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

        $(document).on('click', '.btnEditar', function() {
            var id = $(this).attr("id");

            $.ajax({
                url: "{{ route('admin.users.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $("#formModal #exampleModalLabel").html("Modificar Personal");
                    $("#formModal .modal-body").html(response);
                    $("#formModal").modal("show");

                    // Inicializar eventos para elementos dinámicos
                    const userTypeField = document.getElementById('usertype_id');
                    const licenseField = document.getElementById('licenseField');
                    const licenseInput = document.getElementById('license');
                    const zonaField = document.getElementById('zonaField');
                    const zonaInput = document.getElementById('zone_id');

                    // Función para manejar la visibilidad de los campos según el tipo de usuario
                    function handleUserTypeChange() {
                        const selectedUserType = userTypeField.options[userTypeField.selectedIndex].text
                            .toLowerCase();

                        // Mostrar/Ocultar campo licencia
                        if (selectedUserType === 'conductor') {
                            licenseField.style.display = 'block';
                            licenseInput.required = true;
                        } else {
                            licenseField.style.display = 'none';
                            licenseInput.required = false;
                        }

                        // Mostrar/Ocultar campo zona
                        if (selectedUserType === 'ciudadano') {
                            zonaField.style.display = 'block';
                            zonaInput.required = true;
                        } else {
                            zonaField.style.display = 'none';
                            zonaInput.required = false;
                        }
                    }

                    // Verificación inicial al cargar el modal
                    if (userTypeField) {
                        handleUserTypeChange();

                        // Listener para cambios en el tipo de usuario
                        userTypeField.addEventListener('change', handleUserTypeChange);
                    }

                    // Envío del formulario
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
                                Swal.fire('Proceso exitoso', response.message,
                                    'success');
                            },
                            error: function(xhr) {
                                var response = xhr.responseJSON;
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    });
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
