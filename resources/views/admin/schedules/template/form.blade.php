<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre del horario', 'requerid']) !!}
</div>

<div class="form-group">
    {!! Form::label('time_start', 'Hora inicio') !!}
    {!! Form::input('time', 'time_start', null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('time_end', 'Hora fin') !!}
    {!! Form::input('time', 'time_end', null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control',
        'placeholder' => 'Descripción de la marca',
    ]) !!}
</div>
