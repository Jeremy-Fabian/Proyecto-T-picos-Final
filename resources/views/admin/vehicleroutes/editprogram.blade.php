{!! Form::open(['route' => 'admin.updateAllPrograms']) !!}
@include('admin.vehicleroutes.template.formeditprogram')
<button type="submit" class="btn btn-success"><i class="fas fa-pen-square"></i> Actualizar</button>
<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-arrow-alt-circle-left"></i> Cerrar</button>

{!! Form::close() !!}