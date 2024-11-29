{!! Form::model($usertypes, ['route' => ['admin.usertypes.update', $usertypes], 'method' => 'put']) !!}
@include('admin.usertypes.template.form')
<button type="submit" class="btn btn-success"><i class="fas fa-pen-square"></i> Actualizar</button>
<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-arrow-alt-circle-left"></i> Cerrar</button>

{!! Form::close() !!}