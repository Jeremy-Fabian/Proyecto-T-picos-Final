<div class="form-group">
    {!! Form::label('date_route', 'Fecha') !!}
    {!! Form::input('date','date_route', null, ['class' => 'form-control', 'placeholder' => 'Fecha de registro', 'requerid']) !!}
</div>

<div class="form-group">
    {!! Form::label('time_route', 'Hora') !!}
    {!! Form::input('time', 'time_route', null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('vehicle_id', 'Vehiculo') !!}
    {!! Form::select('vehicle_id', $vehicles, null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('route_id', 'Ruta') !!}
    {!! Form::select('route_id', $routes, null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('schedule_id', 'Horario') !!}
    {!! Form::select('schedule_id', $schedules, null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control',
        'placeholder' => 'Descripción de la marca',
        'rows' => 3,
    ]) !!}
</div>
