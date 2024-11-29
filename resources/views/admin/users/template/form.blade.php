<div class="form-group">
    {!! Form::label('name', 'Nombre Completo') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre completo', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('dni', 'DNI') !!}
    {!! Form::text('dni', null, ['class' => 'form-control', 'placeholder' => 'Documento de Identidad', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('birthdate', 'Fecha de Nacimiento') !!}
    {!! Form::date('birthdate', null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('address', 'Dirección') !!}
    {!! Form::text('address', null, ['class' => 'form-control', 'placeholder' => 'Dirección', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('email', 'Correo Electrónico') !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Correo electrónico', 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('password', 'Contraseña') !!}
    {!! Form::password('password', [
        'class' => 'form-control',
        'placeholder' => '********',
        'disabled' => 'disabled',
        'id' => 'password',
    ]) !!}
    <small class="text-muted">Este campo está bloqueado. Presiona "Cambiar contraseña" para editarla.</small>
    <button type="button" id="btnChangePassword" class="btn btn-sm btn-secondary mt-2">Cambiar contraseña</button>
</div>

<div class="form-group">
    {!! Form::label('usertype_id', 'Tipo de Usuario') !!}
    {!! Form::select('usertype_id', $userTypes, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un tipo de usuario',
        'required',
    ]) !!}
</div>

<div class="form-group">
    {!! Form::label('profile_photo_path', 'Foto de Perfil') !!}
    {!! Form::file('profile_photo_path', ['class' => 'form-control']) !!}
</div>

<script>
    document.getElementById('btnChangePassword').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        passwordField.disabled = !passwordField.disabled; // Alterna entre habilitado y deshabilitado
        if (!passwordField.disabled) {
            passwordField.placeholder = 'Ingrese una nueva contraseña';
            passwordField.value = ''; // Limpia el campo para que se pueda ingresar una nueva contraseña
        } else {
            passwordField.placeholder = '********';
        }
    });
</script>

