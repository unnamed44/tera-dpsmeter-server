<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDpsData;
use App\Stat;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;

class DpsController extends Controller
{
    public function takeDpsSubmit(StoreDpsData $request)
    {
        $data = json_decode($request->getContent());

        $statEntity = new Stat();

        $statEntity->encounter_unix = $data->encounterUnixEpoch;
        $statEntity->area_id = $data->areaId;
        $statEntity->boss_id = $data->bossId;
        $statEntity->meter_name = $data->meterName;
        $statEntity->meter_version = $data->meterVersion;
        $statEntity->data = $data;

        try {
            $statEntity->save();
        } catch (QueryException $queryException) {
            if ($queryException->getCode() == 23000) { // Duplicate key
                $existing = Stat::findExisting($statEntity);

                return response()->json([
                    'message' => 'This encounter has already been saved',
                    'id' => $existing->id,
                ]);
            }
            throw $queryException;
        }

        return response()->json([
            'message' => 'Encounter saved! Thanks!',
            'id' => $statEntity->id,
        ], Response::HTTP_CREATED);
    }
}