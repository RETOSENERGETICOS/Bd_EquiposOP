<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Family;
use App\Models\File;
use App\Models\Group;
use App\Models\Set;
use App\Models\Des;
use App\Models\Calibration;
use App\Models\Location;
use App\Models\Log;
use App\Models\Tool;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ToolController extends Controller
{

    public function history(Request $request) {
        return response()->json(Log::with('tool','user')->orderBy('created_at', 'desc')->limit(1000)->get());
    }

    public function index(Request $request) {
        return response()->json(
            Tool::all()->map(static function(Tool $tool) {
                return [
                    'id' => $tool->id,
                    'item' => $tool->item,
                    'set' => $tool->set,
                    'des' => $tool->des,
                    'brand' => $tool->brand,
                    'calibration' => $tool-calibration,
                    'location' => $tool->location,
                    'serial_number' => $tool->serial_number,
                    'calibration_expiration' => $tool->calibration_expiration
                ];
            })
        );
    }

    public function show(Request $request, Tool $tool) {
        return response()->json([
            'id' => $tool->id,
            'item' => $tool->item,
            'set' => $tool->set,
            'des' => $tool->des,
            'brand' => $tool->brand,
            'calibration' => $tool-calibration,
            'location' => $tool->location,
            'quantity' => $tool->quantity,
            'measurement' => $tool->measurement,
            'serial_number' => $tool->serial_number,
            'spect' => $tool->spect,
            'model' => $tool->model,
            'shelf_localization' => $tool->shelf_localization,
            'comments' => $tool->comments,
            'files' => $tool->files->map(static function(File $file) {
                return $file->path;
            })
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $tool = $this->createTool($request);
            foreach ($request->documents as $document) {
                $tool->files()->create([
                    'path' => $document
                ]);
            }
            $request->user()->logs()->create([
                'tool_id' => $tool->id,
                'comment' => 'Se inserto registro',
                'type'=> 'insert',
                'after' => json_encode($this->getValues($tool->toArray(), $tool))
            ]);
            DB::commit();
            return response()->json(
                $tool
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, Tool $tool) {
        DB::beginTransaction();
        try {
            $request->user()->logs()->create([
                'tool_id' => $tool->id,
                'comment' => 'Se elimino registro',
                'type'=> 'delete',
            ]);
            $tool->deleteOrFail();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
        DB::commit();
        return response()->json($tool);
    }

    public function update(Request $request, Tool $tool) {
        DB::beginTransaction();
        try {
            $set = $this->getSet($request->set);
            $des = $this->getDes($request->des);
            $calibration = $this->getCalibration($request->calibration);
            $location = $this->getLocation($request->location);
            $brand = $this->getBrand($request->brand);
            $oldTool = json_encode($this->getValues($tool->toArray(), $tool));
            if ($request->location !== $tool->location) {
                $tool->update([ 'quantity' => $tool->quantity - $request->movingQuantity ]);
                $request->quantity = $request->movingQuantity;
                $tool = $this->createTool($request);
                $request->user()->logs()->create([
                    'tool_id' => $tool->id,
                    'comment' => 'Se traspaso registro',
                    'type'=> 'update',
                    'before' => $oldTool,
                    'after' => json_encode($this->getValues($tool->toArray(), $tool))
                ]);
            } else {
                $tool->update([
                    'set_id' => $set->id ?? null,
                    'des_id' => $des->id ?? null,
                    'brand_id' => $brand->id ?? null,
                    'calibration_id' => $calibration->id ?? null,
                    'location_id' => $location->id ?? null,
                    'quantity' => $request->quantity,
                    'measurement' => $request->measurement,
                    'serial_number' => $request->serial,
                    'spect' => $request->spect,
                    'model' => $tool->model,
                    'shelf_localization' => $request->shelf_localization,
                    'comments' => $request->comments
                ]);
                $oldValues = $tool->getChanges();
                if (count($oldValues) > 0) {
                    $request->user()->logs()->create([
                        'tool_id' => $tool->id,
                        'comment' => 'Se edito registro',
                        'type'=> 'update',
                        'before' => $oldTool,
                        'after' => json_encode($this->getValues($oldValues, $tool->refresh()))
                    ]);
                }
            }
            DB::commit();
            return response()->json(
                $tool
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    private function createTool(Request $request) {
        $set = $this->getSet($request->set);
        $des = $this->getDes($request->des);
        $brand = $this->getBrand($request->brand);
        $calibration = $this->getCalibration($request->calibration);
        $location = $this->getLocation($request->location);
        $tool = $request->user()->tools()->create([
            'set_id' => $set->id ?? null,
            'des_id' => $des->id ?? null,
            'brand_id' => $brand->id ?? null,
            'calibration_id' => $calibration->id ?? null,
            'location_id' => $location->id ?? null,
            'quantity' => $request->quantity,
            'measurement' => $request->measurement,
            'serial_number' => $request->serial,
            'spect' => $request->spect,
            'model' => $tool->model,
           'shelf_localization' => $request->shelf_localization,
            'comments' => $request->comments
        ]);
        $tool->update([
            'item' => sprintf('AAA%04d', $tool->id)
        ]);
        return $tool->refresh();
    }

    private function getValues($values, Tool $tool) {
//        dd($values, $tool);
        $specialAttributes = ['set_id' => 'set','des_id' => 'des','brand_id' => 'brand','calibration_id' => 'calibration','location_id' => 'location'];
        $names = ['item' => 'Item','set_id' => 'Equipo','des_id' => 'Descripcion','brand_id' => 'Marca','calibration_id' => 'Calibracion', 'location_id'=> 'Localizacion',
            'quantity' => 'Cantidad/QTY','measurement' => 'Unidad de contaje','serial_number' => 'N de serie','spect' => 'Caracteristicas',
            'model' => 'Modelo/Model', 'shelf_localization' => 'Localizacion 2', 'comments' => 'Comentarios'];
        $data = array();
        foreach (array_keys($values) as $key) {
            if (array_key_exists($key, $specialAttributes)) {
                $data[$names[$key]] = $tool[$specialAttributes[$key]]->name ?? '';
            } else if(array_key_exists($key, $names)){
                $data[$names[$key]] = $values[$key];
            }
        }
        return $data;
    }

    public function showTool(Tool $tool): array {
        return [
            'id' => $tool->id,
            'item' => $tool->item,
            'set' => $tool->set,
            'des' => $tool->des,
            'brand' => $tool->brand,
            'calibration' => $tool-calibration,
            'location' => $tool->location,
            'quantity' => $tool->quantity,
            'measurement' => $tool->measurement,
            'serial_number' => $tool->serial_number,
            'spect' => $tool->spect,
            'model' => $tool->model,
            'shelf_localization' => $tool->shelf_localization,
            'user' => $tool->user
        ];
    }

    public function search(Request $request) {
        $especialKeys = ['set','des','brand','calibration','location','user'];
        $filters = $request->keys();
        $query = Tool::query();
        foreach($filters as $filter) {
            if (in_array($filter, $especialKeys, true)) {
                $value = is_null($request[$filter]) ? null : $request[$filter]['id'];
                if ($value !== 0) {
                    $query = $query->where("{$filter}_id", is_null($request[$filter]) ? null : $request[$filter]['id']);
                }
            }
            else if (!in_array($filter, $especialKeys, true)){
                $query = $query->where(Str::snake($filter), 'like', "%$request[$filter]%");
            }
        }
        $data = $query->get()->map(function(Tool $tool) {
            return $this->showTool($tool);
        });
        return response()->json($data);
    }

    private function getSet($data)
    {
        if (is_null($data)) {
            return null;
        }
        if (is_array($data)) {
            return Set::find($data['id']);
        }
        return Set::where('name', $data)->firstOrCreate([
            'name' => $data
        ]);
    }

    private function getDes($data)
    {
        if (is_null($data)) {
            return null;
        }
        if (is_array($data)) {
            return Des::find($data['id']);
        }
        return Des::where('name', $data)->firstOrCreate([
            'name' => $data
        ]);
    }

    private function getBrand($data)
    {
        if (is_null($data)) {
            return null;
        }
        if (is_array($data)) {
            return Brand::find($data['id']);
        }
        return Brand::where('name', $data)->firstOrCreate([
            'name' => $data
        ]);
    }

    private function getCalibration($data)
    {
        if (is_null($data)) {
            return null;
        }
        if (is_array($data)) {
            return Calibration::find($data['id']);
        }
        return Calibration::where('name', $data)->firstOrCreate([
            'name' => $data
        ]);
    }

    private function getLocation($data)
    {
        if (is_null($data)) {
            return null;
        }
        if (is_array($data)) {
            return Location::find($data['id']);
        }
        return Location::where('name', $data)->firstOrCreate([
            'name' => $data
        ]);
    }
}
