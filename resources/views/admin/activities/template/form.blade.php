<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre de la actividad', 'requerid']) !!}
</div>

<div class="form-group">
    {!! Form::label('date_start', 'Fecha incio') !!}
    {!! Form::input('date','date_start', null, ['class' => 'form-control', 'placeholder' => 'Fecha inicio', 'requerid']) !!}
</div>

<div class="form-group">
    {!! Form::label('date_end', 'Fecha fin') !!}
    {!! Form::input('date','date_end', null, ['class' => 'form-control', 'placeholder' => 'Fecha fin', 'requerid']) !!}
</div>