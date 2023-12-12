<?php

namespace App\Http\Controllers;

use App\Models\enfermedad_viral;
use App\Models\enfermedad_viral_mapa;
use App\Models\estadia_enfermedad;
use App\Models\mapa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mapas = mapa::paginate(10);
        return view('analisis.mapas.index', compact('mapas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $enfermedades = enfermedad_viral::all();
        return view('analisis.mapas.create', compact('enfermedades'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $mapa = mapa::create($request->all());
        $mapa->enfermedad_virals()->attach($request->enfermedadesID, ['created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('mapas.index')->with('success', 'ok');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mapa  $mapa
     * @return \Illuminate\Http\Response
     */
    public function show(Mapa $mapa)
    {
        //dd($mapa);
        $enfermedadesIds = $mapa->enfermedad_virals->pluck('id')->toArray();
        
        if ($mapa->fecha_ini == null){
            $userIds = DB::table('estadia_enfermedads')
            ->join('estados', 'estadia_enfermedads.estado_id', '=', 'estados.id')
            ->select('estadia_enfermedads.user_id')
            ->where('estados.estado', 'Confirmado')
            ->whereIn('enfermedad_id', $enfermedadesIds)
            ->get();

        }else{ 
            $userIds = DB::table('estadia_enfermedads')
            ->join('estados', 'estadia_enfermedads.estado_id', '=', 'estados.id')
            ->select('estadia_enfermedads.user_id')
            ->where('estados.estado', 'Confirmado')
            ->whereIn('enfermedad_id', $enfermedadesIds)
            ->whereBetween('estadia_enfermedads.fecha_ini', [$mapa->fecha_ini, $mapa->fecha_fin])
            ->get();
        }
        
        //dd( $userIds[0]);
        $userIdsArray = collect($userIds)->pluck('user_id')->toArray();
        //dd( $userIdsArray);
        $puntos = User::whereIn('id', $userIdsArray)
            ->whereNotNull('latitud')
            ->select('id', 'latitud', 'longitud')
            ->get();
    
        return view('analisis.mapas.show',compact('mapa','puntos'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mapa  $mapa
     * @return \Illuminate\Http\Response
     */
    public function edit(Mapa $mapa)
    {
        $enfermedades = enfermedad_viral::all();
        $enferGuardadas = $mapa->enfermedad_virals->pluck('id');
        return view('analisis.mapas.edit', compact('enfermedades', 'mapa', 'enferGuardadas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mapa  $mapa
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Mapa $mapa)
    {
        $nuevosValores = $request->only(['name', 'detalle', 'latitud', 'longitud','fecha_ini','fecha_fin']);
        $mapa->update($nuevosValores);
        $mapa->enfermedad_virals()->detach();
        $mapa->enfermedad_virals()->attach($request->enfermedadesID, ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('mapas.edit',$mapa)->with('success', 'ok');
    }



    public function destroy(Mapa $mapa)
    {
        $mapa->enfermedad_virals()->detach();
        mapa::destroy($mapa->id);
        return redirect()->route('mapas.index',$mapa);
    }

    public function comparar(Request $request)
    {
        //dd($request);
        $mapa1 = Mapa::find($request->map1);
        $mapa2 = Mapa::find($request->map2);

        //dd($map2);
        $enfermedadesIds1 = $mapa1->enfermedad_virals->pluck('id')->toArray();
        $enfermedadesIds2 = $mapa2->enfermedad_virals->pluck('id')->toArray();

        if ($mapa1->fecha_ini == null){

            $userIds1 = DB::table('estadia_enfermedads')
            ->join('estados', 'estadia_enfermedads.estado_id', '=', 'estados.id')
            ->select('estadia_enfermedads.user_id')
            ->where('estados.estado', 'Confirmado')
            ->whereIn('enfermedad_id', $enfermedadesIds1)
            ->get();

        }else{ 
            $userIds1 = DB::table('estadia_enfermedads')
            ->join('estados', 'estadia_enfermedads.estado_id', '=', 'estados.id')
            ->select('estadia_enfermedads.user_id')
            ->where('estados.estado', 'Confirmado')
            ->whereIn('enfermedad_id', $enfermedadesIds1)
            ->whereBetween('estadia_enfermedads.fecha_ini', [$mapa1->fecha_ini, $mapa1->fecha_fin])
            ->get();
        }
        

        if ($mapa2->fecha_ini == null){
            $userIds2 = DB::table('estadia_enfermedads')
            ->join('estados', 'estadia_enfermedads.estado_id', '=', 'estados.id')
            ->select('estadia_enfermedads.user_id')
            ->where('estados.estado', 'Confirmado')
            ->whereIn('enfermedad_id', $enfermedadesIds2)
            ->get();

        }else{ 
            $userIds2 = DB::table('estadia_enfermedads')
            ->join('estados', 'estadia_enfermedads.estado_id', '=', 'estados.id')
            ->select('estadia_enfermedads.user_id')
            ->where('estados.estado', 'Confirmado')
            ->whereIn('enfermedad_id', $enfermedadesIds2)
            ->whereBetween('estadia_enfermedads.fecha_ini', [$mapa2->fecha_ini, $mapa2->fecha_fin])
            ->get();
        }


        $userIdsArray1 = collect($userIds1)->pluck('user_id')->toArray();
        $userIdsArray2 = collect($userIds2)->pluck('user_id')->toArray();

        $puntos1 = User::whereIn('id', $userIdsArray1)
            ->whereNotNull('latitud')
            ->select('id', 'latitud', 'longitud')
            ->get();
    
        $puntos2 = User::whereIn('id', $userIdsArray2)
            ->whereNotNull('latitud')
            ->select('id', 'latitud', 'longitud')
            ->get();

        //dd($puntos1, $puntos2);    
        return view('analisis.mapas.comparar', compact('mapa1','puntos1','mapa2','puntos2'));
    }

}
