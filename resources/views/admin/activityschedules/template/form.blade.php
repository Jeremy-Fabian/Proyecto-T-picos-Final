{!! Form::hidden('activity_id', $activity_id) !!}
<div class="form-group">
    {!! Form::label('day', 'Dia') !!}
    {!! Form::select('day', [
        'Lunes' => 'Lunes',
        'Martes' => 'Martes',
        'Miércoles' => 'Miércoles',
        'Jueves' => 'Jueves',
        'Viernes' => 'Viernes',
        'Sábado' => 'Sábado',
        'Domingo' => 'Domingo',
    ], null, ['class' => 'form-control', 'placeholder' => 'Selecciona un día', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('vehicle_id', 'Vehículo') !!}
    {!! Form::select('vehicle_id', $vehicles, null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('user_id', 'Conductor') !!}
    {!! Form::select('user_id', $drivers, null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('type', 'Tipo') !!}
    {!! Form::select('type', [
        'Limpieza' => 'Limpieza',
        'Reparación' => 'Reparación',
    ], null, ['class' => 'form-control', 'placeholder' => 'Selecciona un tipo', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('time_start', 'Hora incio') !!}
    {!! Form::input('time','time_start', null, ['class' => 'form-control', 'placeholder' => 'Hora inicio', 'requerid']) !!}
</div>

<div class="form-group">
    {!! Form::label('time_end', 'Hora fin') !!}
    {!! Form::input('time','time_end', null, ['class' => 'form-control', 'placeholder' => 'Hora fin', 'requerid']) !!}
</div>


<script>
    $("#vehicle_id").change(function() {
        var id = $(this).val();

        $.ajax({
            url: "{{ route('admin.userdriver', '_id') }}".replace('_id', id),
            type: "GET",
            datatype: "JSON",
            contentype: "application/json",
            success: function(response) {
                $("#user_id").empty();
                $.each(response, function(key, value) {
                    $("#user_id").append("<option value=" + value.user_id + ">" + value.name +
                        "</option>");
                });
                console.log(response);

            }
        });
    });
</script>