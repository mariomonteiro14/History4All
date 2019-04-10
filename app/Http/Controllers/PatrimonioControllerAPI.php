<?php

namespace App\Http\Controllers;

use App\Patrimonio;
use App\PatrimonioImagens;
use Illuminate\Http\Request;
use App\Http\Resources\Patrimonio as PatrimonioResource;
use Illuminate\Support\Facades\Storage;

class PatrimonioControllerAPI extends Controller
{
    public function patrimoniosDataTable(Request $request)
    {
        $patrim = PatrimonioResource::collection(Patrimonio::all());
        /*foreach ($patrim as $patr){
            $patr['foto'] = PatrimonioImagens::where('patrimonio_id', $patr->id)->first()->foto;
        }*/
        return response()->json([
            'data' => $patrim,
        ]);
    }

    public function find(Request $request, $id){
        return Patrimonio::with('imagens')->where('id', $id)->first();
    }

    public function update(Request $request, $id){

        $patrim = Patrimonio::findOrFail($id);
        $patrim->fill($request->all());
        $patrim->save();

        if($request->has('novas_imagens')) {
            foreach ($request->novas_imagens as $image) {
                $filename = str_random(8) . '.' . $image->getClientOriginalExtension();;
                Storage::disk('public')->putFileAs('patrimonios', $image, $filename);
                $patrim_image = new PatrimonioImagens();

                $patrim_image->patrimonio_id = $patrim->id;
                $patrim_image->foto = $filename;

                $patrim_image->save();
            }
        };

        if($request->has('eliminar_imagens')) {
            foreach ($request->novas_imagens as $image_nome) {
                $image = PatrimonioImagens::where('foto', '$image_nome')->get();
                Storage::disk('public')->delete('patrimonios/'.$image_nome);
                $image->delete();
            }
        }

        return response()->json(new PatrimonioResource($patrim), 200);
    }

    public function store(Request $request){

        $request->validate([
            'nome' => 'required|min:3|max:255',
            'descricao' => 'required|min:30|max:3000',
            'distrito' => 'required',
            'epoca' => 'required',
            'ciclo' => 'required',
            'imagens.*' => 'nullable|file|mimes:jpeg,bmp,png,jpg'
        ]);

        $patrim = new Patrimonio();
        $patrim->fill($request->all());
        $patrim->save();

        if($request->has('imagens')) {
            foreach ($request->imagens as $image) {
                $filename = str_random(8) . '.' . $image->getClientOriginalExtension();;
                Storage::disk('public')->putFileAs('patrimonios', $image, $filename);
                $patrim_image = new PatrimonioImagens();

                $patrim_image->patrimonio_id = $patrim->id;
                $patrim_image->foto = $filename;

                $patrim_image->save();
            }
        }
        return response()->json(new PatrimonioResource($patrim), 201);

    }

    public function destroy($id)
    {
        $patrim = Patrimonio::findOrFail($id);
        $images = PatrimonioImagens::where('patrimonio_id', $patrim->id)->pluck('foto');
        foreach ($images as $image) {
            Storage::disk('public')->delete('patrimonios/'.$image);
        }
        $patrim->delete();
        return response()->json(null, 201);
    }

}
