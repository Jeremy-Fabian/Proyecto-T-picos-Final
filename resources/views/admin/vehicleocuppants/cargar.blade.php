{{-- resources\views\admin\vehicleocuppants\cargar.blade.php --}}
{{-- {!! Form::open(['route' => 'admin.vehicleocuppants.store']) !!}
    @include('admin.vehicleocuppants.template.form')
{!! Form::close() !!}
 --}}
<input type="hidden" id="vehicle_id" value="{{ $vehicle->id }}">
<div class="form-row">

    <div class="form-group col-11">
        <label for="conductor" id="labelConductor">{{ $contC == 0 ? 'Conductores' : 'No Conductores' }}</label>
        <select id="conductor_id" class="form-control">
            @foreach ($listaSeletector as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-1" style="padding-top: 30px;">
        <button id="btnGuardarOcu" type="button" class="btn btn-success"><i class="fas fa-plus"></i></button>
    </div>

</div>

<div class="card-body table-responsive">
    <table class="table table-striped" id="datatable-ocupa">
        <thead>
            <tr>
                <th>ID</th>
                <th>NOMBRE</th>
                <th>TIPO</th>
                <th width="10"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ocuppantes as $ocuppante)
                <tr>
                    <td>{{ $ocuppante->id }}</td>
                    <td>{{ $ocuppante->name_user }}</td>
                    <td>{{ $ocuppante->name_tipo }}</td>
                    <td>
                        <button data-id="{{ $ocuppante->id }}"
                            class="btn btn-sm btn-danger btnEliminar">ELIMINAR</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


<script>
    $(document).ready(function() {
        const contC = {{ $contC }};
        const contNoC = {{ $contNoC }};
        const capacity = {{ $capacity }};
        updateValidadores(contC, contNoC, capacity);
    });

    $('#btnGuardarOcu').click(function(e) {
        e.preventDefault();

        // Obtener los valores seleccionados de los campos
        var vehicle_id = $('#vehicle_id').val();
        var conductor_id = $('#conductor_id').val();
        var no_conductor_id = $('#no_conductor_id').val();

        var formData = {
            vehicle_id: vehicle_id,
            conductor_id: conductor_id,
            no_conductor_id: no_conductor_id,
            _token: "{{ csrf_token() }}"
        };

        $.ajax({
            url: "{{ route('admin.vehicleocuppants.store') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire('Éxito', response.message, 'success');

                updateTable(response.datos.ocuppantes);
                updateValidadores(response.datos.contC, response.datos.contNoC, response.datos
                    .capacity);
                updateSelectOptions(response.datos.listaSeletector);
            },
            error: function(xhr) {
                var response = xhr.responseJSON;
                Swal.fire('Error', response ? response.message :
                    'Ocurrió un error al registrar el ocupante', 'error');
            }
        });
    });


    $(document).on('click', '.btnEliminar', function(e) {
        e.preventDefault();
        const occupanteId = $(this).data('id');
        const url = "{{ route('admin.vehicleocuppants.destroyer', ':occupanteId') }}".replace(':occupanteId',
            occupanteId);

        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esto.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        Swal.fire('Éxito', response.message, 'success');
                        updateTable(response.datos.ocuppantes);
                        updateValidadores(response.datos.contC, response.datos.contNoC,
                            response.datos.capacity);
                        updateSelectOptions(response.datos.listaSeletector);
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response ? response.message :
                            'Error al eliminar', 'error');
                    }
                });
            }
        });
    });




    function updateTable(ocuppantes) {
        $('#datatable-ocupa tbody').empty();
        $.each(ocuppantes, function(index, occupante) {
            const newRow = `<tr>
                <td>${occupante.id}</td>
                <td>${occupante.name_user}</td>
                <td>${occupante.name_tipo}</td>
                <td>
                    <button data-id="${occupante.id}" class="btn btn-sm btn-danger btnEliminar">ELIMINAR</button>
                </td>
            </tr>`;
            $('#datatable-ocupa tbody').append(newRow);
        });
    }

    function updateSelectOptions(conductorData) {
        $('#conductor_id').empty();
        $.each(conductorData, function(id, name) {
            $('#conductor_id').append(new Option(name, id));
        });

    }

    function updateValidadores(contC, contNoC, capacity) {
        const labelConductor = $('#labelConductor');
        const formRow = $('.form-row');
        if (capacity > 0 && contC + contNoC < capacity) {
            labelConductor.text(contC === 0 ? 'Conductores' : 'No Conductores');
            formRow.show();
        } else {
            formRow.hide();
        }
    }
</script>
