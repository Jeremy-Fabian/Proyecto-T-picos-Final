<div class="form-row">
    <div class="form-group col-6">
        {!! Form::label('date_start', 'Fecha Inicio') !!}
        {!! Form::input('date','date_start', null, ['class' => 'form-control', 'placeholder' => 'Fecha de registro', 'requerid']) !!}
    </div>

    <div class="form-group col-6">
        {!! Form::label('date_end', 'Fecha Fin') !!}
        {!! Form::input('date','date_end', null, ['class' => 'form-control', 'placeholder' => 'Fecha de registro', 'requerid']) !!}
    </div>

    <div class="form-group col-6">
        {!! Form::label('time_route', 'Hora') !!}
        {!! Form::input('time', 'time_route', null, ['class' => 'form-control', 'required']) !!}
    </div>
    
    <div class="form-group col-6">
        {!! Form::label('vehicle_id', 'Vehiculo') !!}
        {!! Form::select('vehicle_id', $vehicles, null, ['class' => 'form-control', 'required']) !!}
    </div>
    
    <div class="form-group col-6">
        {!! Form::label('route_id', 'Ruta') !!}
        {!! Form::select('route_id', $routes, null, ['class' => 'form-control', 'required']) !!}
    </div>
    
    <div class="form-group col-6">
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
