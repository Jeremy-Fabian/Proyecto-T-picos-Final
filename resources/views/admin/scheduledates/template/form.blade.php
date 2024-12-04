<div class="form-row">
    <div class="form-group col-5">
        {!! Form::hidden('activityschedule_id', $activityschedule_id) !!}
        <div class="form-group">
            {!! Form::label('date', 'Fecha') !!}
            {!! Form::input('date','date', null, ['class' => 'form-control', 'placeholder' => 'Fecha', 'requerid']) !!}
        </div>

        <div class="form-group">
            {!! Form::file('image', [
                'class' => 'form-control-file', // Oculta el input
                'accept' => 'image/*',
                'id' => 'imageInput',
            ]) !!}

        </div>

        <div class="form-group">
            {!! Form::label('description', 'Descripción') !!}
            {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'placeholder' => 'Descripción',
            ]) !!}
        </div>
    </div>

    <div class="form-group col-7">
        <div class="p-2"></div>
            <div class="card">
                <div class="card-header">
                    <h5>Fechas de los mantenimientos por horario</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped" id="datatable2">
                        <thead>
                            <tr>
                                <th>FECHA</th>
                                <th>DESCRIPCION</th>
                                <th>IMAGE</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var activityschedule_id = $('input[name="activityschedule_id"]').val();
        // Inicializar DataTable dentro del modal
        var table2 = $('#datatable2').DataTable({
                        "ajax": "{{ route('admin.scheduledates.show', 'id') }}".replace('id', activityschedule_id),
                        "columns": [
                            { 
                                "data": "date",
                            },
                            { 
                                "data": "description",
                            },
                            {
                                "data": "img",
                            }
                        ],
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                        }
                    });
    });

</script>