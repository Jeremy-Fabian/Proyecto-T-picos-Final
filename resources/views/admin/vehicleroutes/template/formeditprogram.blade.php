<div class="form-row">

    <div class="form-group col-12">
        {!! Form::label('vehicle_id', 'Seleccione vehiculo para editar:') !!}
        {!! Form::select('vehicle_id', $vehicles, null, ['class' => 'form-control', 'required']) !!}
    </div>

    <div class="form-group col-6">
        {!! Form::label('date_start', 'Fecha Inicio') !!}
        {!! Form::input('date', 'date_start', null, ['class' => 'form-control', 'placeholder' => 'Fecha de registro', 'required']) !!}
    </div>

    <div class="form-group col-6">
        {!! Form::label('date_end', 'Fecha Fin') !!}
        {!! Form::input('date', 'date_end', null, ['class' => 'form-control', 'placeholder' => 'Fecha de registro', 'required']) !!}
    </div>

    <div class="form-group col-6">
        {!! Form::label('time_route', 'Hora') !!}
        {!! Form::input('time', 'time_route', null, ['class' => 'form-control', 'required']) !!}
    </div>

    <div class="form-group col-6">
        {!! Form::label('route_id', 'Ruta') !!}
        {!! Form::select('route_id', $routes, null, ['class' => 'form-control', 'required']) !!}
    </div>
    
    <div class="form-group col-12">
        {!! Form::label('schedule_id', 'Horario') !!}
        {!! Form::select('schedule_id', $schedules, null, ['class' => 'form-control', 'required']) !!}
    </div>
    
    <div class="form-group col-12">
        {!! Form::label('description', 'Descripción') !!}
        {!! Form::textarea('description', null, [
            'class' => 'form-control',
            'placeholder' => 'Descripción de la programación',
        ]) !!}
    </div>
</div>


<script>
    $(document).on('change', '#vehicle_id', function() {
    var vehicle_id = $(this).val();  // Obtener el ID del vehículo seleccionado

    if (vehicle_id) {
        // Hacer la solicitud para obtener las fechas correspondientes al vehículo
        $.ajax({
            url: "{{ route('admin.getVehicleDates', '_id') }}".replace('_id', vehicle_id),
            type: "GET",
            data: {
                vehicle_id: vehicle_id  // Enviar el ID del vehículo
            },
            success: function(response) {
                // Aquí directamente actualizamos los campos del formulario en el modal
                $('#date_start').val(response.date_start);  // Actualizar 'Fecha Inicio'
                $('#date_end').val(response.date_end);      // Actualizar 'Fecha Fin'
                $('#time_route').val(response.time);
                $('#route_id').val(response.rout);
                $('#schedule_id').val(response.sched);
                $('#description').val(response.descript);
                // Mostrar el modal
                $("#formModal").modal("show");
            },
            error: function(xhr) {
            }
        });
    }
});
</script>