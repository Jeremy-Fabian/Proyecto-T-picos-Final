{{-- resources\views\admin\vehicleocuppants\template\form.blade.php --}}

{!! Form::hidden('vehicle_id', $vehicle->id) !!}
<div class="form-row">
    @if ($contC == 0)
        <div class="form-group col-11">
            <label for="conductor">Conductor</label>
            <select id="conductor" name="conductor_id" class="form-control">
                @foreach ($Conduc as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-1" style="padding-top: 30px;">
            <button id="btnadd" type="submit" class="btn btn-success"><i class="fas fa-plus"></i></button>
        </div>
    @endif

    @if ($contC != 0 && $contNoC < $capacity - 1 )
        <div class="form-group col-11">
            <label for="conductor">No Conductor</label>
            <select id="conductor" name="no_conductor_id" class="form-control">
                @foreach ($NoConduc as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-1" style="padding-top: 30px;">
            <button id="btnadd" type="submit" class="btn btn-success"><i class="fas fa-plus"></i></button>
        </div>
    @endif
</div>
