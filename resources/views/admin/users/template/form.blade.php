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
        'disabled' => isset($user),
        'id' => 'password',
    ]) !!}
    <small class="text-muted"></small>
    @if (isset($user) && $user->exists)
        <button type="button" id="btnChangePassword" class="btn btn-sm btn-secondary mt-2">Cambiar contraseña</button>
    @endif
</div>

<div class="form-group">
    {!! Form::label('usertype_id', 'Tipo de Usuario') !!}
    {!! Form::select('usertype_id', $userTypes, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un tipo de usuario',
        'required',
        'id' => 'usertype_id',
    ]) !!}
</div>

<div class="form-group" id="licenseField" style="display: none;">
    {!! Form::label('license', 'Licencia de Conducir') !!}
    {!! Form::text('license', null, [
        'class' => 'form-control',
        'placeholder' => 'Ingrese la licencia de conducir (Ej: A1234567)',
        'pattern' => '^[A-Za-z0-9]{8}$', // Validación con regex
        'title' => 'La licencia debe tener 8 caracteres alfanuméricos.',
        'id' => 'license',
    ]) !!}
</div>
<div class="form-group" id="zonaField" style="display: none;">
    {!! Form::label('zone_id', 'Zona') !!}
    {!! Form::select('zone_id', $zones, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione una zona',
        'required',
        'id' => 'zone_id',
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
    // document.getElementById('usertype_id').addEventListener('change', function () {
    //     const userType = this.options[this.selectedIndex].text.toLowerCase(); // Obtiene el texto del tipo
    //     const licenseField = document.getElementById('licenseField');
    //     const zonaField = document.getElementById('zonaField');
        
    //     if (userType === 'Conductor') {
    //         licenseField.style.display = 'block';
    //         document.getElementById('license').required = true; // Hacer obligatorio
    //     } else {
    //         licenseField.style.display = 'none';
    //         document.getElementById('license').required = false; // Quitar obligatoriedad
    //     }
    //     if (userType === 'Ciudadano') {
    //         licenseField.style.display = 'block';
    //         document.getElementById('zone_id').required = true; // Hacer obligatorio
    //     } else {
    //         licenseField.style.display = 'none';
    //         document.getElementById('zone_id').required = false; // Quitar obligatoriedad
    //     }


    // });
</script>
{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnChangePassword = document.getElementById('btnChangePassword');
        if (btnChangePassword) {
            btnChangePassword.addEventListener('click', function() {
                const passwordField = document.getElementById('password');
                passwordField.disabled = !passwordField.disabled;
                if (!passwordField.disabled) {
                    passwordField.placeholder = 'Ingrese una nueva contraseña';
                    passwordField.value = '';
                } else {
                    passwordField.placeholder = '********';
                }
            });
        }
    });
</script> --}}
