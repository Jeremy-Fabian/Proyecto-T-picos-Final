<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre del color', 'requerid']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control',
        'placeholder' => 'Descripción del color',
    ]) !!}
</div>

<div class="form-group">
    {!! Form::label('color', 'Color') !!}
    {!! Form::input('color', 'color', null, ['class' => 'form-control', 'placeholder' => 'Selecciona un color', 'required']) !!}
</div>
