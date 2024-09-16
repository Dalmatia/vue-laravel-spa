<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllOutfitsCollection;
use App\Models\Outfit;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Support\Facades\Validator;

class OutfitController extends Controller
{
    // FileServiceのインスタンス化
    private $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    private function validateRequest(Request $request)
    {
        $rules = [
            'file' => 'required | mimes:jpg,jpeg,png',
            'description' => 'nullable',
            'outfit_date' => 'required',
            'season' => 'nullable',
            'tops' => 'nullable |exists:items,id',
            'outer' => 'nullable |exists:items,id',
            'bottoms' => 'nullable |exists:items,id',
            'shoes' => 'nullable |exists:items,id'
        ];

        // 必須フィールドが空でない場合は、必須バリデーションを適用する
        if ($request->filled('file')) {
            $rules['file'] = 'required|mimes:jpg,jpeg,png';
        }

        return Validator::make($request->all(), $rules);
    }

    // /**
    //  * アイテムのフィールドを更新
    //  */
    private function createOrUpdateOutfit(Outfit $outfit, Request $request)
    {
        $outfit->user_id = auth()->user()->id;
        // ファイルが選択されているかどうかをチェックし、選択されている場合のみ更新する
        if ($request->hasFile('file')) {
            $outfit = $this->fileService->updateFile($outfit, $request, 'outfit');
        } elseif ($request->input('file') === null && $outfit->file !== null) {
            // ファイルが選択されていない場合は以前のファイルを保持する
            $outfit->file = $outfit->file;
        }

        if ($request->filled('description')) {
            $outfit->description =  ($request->description !== 'null') ? $request->description : null;
        }

        if ($request->filled('outfit_date')) {
            $outfit->outfit_date = $request->outfit_date;
        }

        if ($request->filled('season')) {
            $outfit->season =  ($request->season !== 'null') ? $request->season : null;
        }

        if ($request->filled('tops')) {
            $outfit->tops = ($request->tops !== 'null') ? $request->tops : null;
        }

        if ($request->filled('outer')) {
            $outfit->outer = ($request->outer !== 'null') ? $request->outer : null;
        }

        if ($request->filled('bottoms')) {
            $outfit->bottoms = ($request->bottoms !== 'null') ? $request->bottoms : null;
        }

        if ($request->filled('shoes')) {
            $outfit->shoes = ($request->shoes !== 'null') ? $request->shoes : null;
        }

        return $outfit;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Outfit::with('items')->orderBy('outfit_date', 'desc');

        // フィルター条件がある場合に適用
        if ($request->filled('main_category')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('main_category', $request->input('main_category'));
            });
        }

        if ($request->filled('sub_category')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('sub_category', $request->input('sub_category'))->orWhereNull('sub_category');
            });
        }

        if ($request->filled('color')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('color', $request->input('color'));
            });
        }

        if ($request->filled('season')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('season', $request->input('season'))->orWhereNull('season');
            });
        }

        $outfits = $query->get();

        return response(['outfits' => new AllOutfitsCollection($outfits), 'users' => User::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $outfit = new Outfit();
        $outfit->user_id = auth()->user()->id;
        $outfit = (new FileService)->updateFile($outfit, $request, 'outfit');
        $outfit->description = $request->input('description');

        // コーディネートした日付を選択する
        $outfit->outfit_date = $request->input('outfit_date');
        // 指定された日付に既に投稿があるか確認
        $validator->after(function ($validator) use ($outfit) {
            if (Outfit::onDate($outfit->outfit_date)->where('user_id', $outfit->user_id)->exists()) {
                $validator->errors()->add('outfit_date', '同じ日付に複数の投稿はできません。');
            }
        });

        // バリデーションが失敗した場合（2回目のチェック）
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // コーディネートのシーズンを選択
        $outfit->season = $request->input('season');
        // 着用したアイテムをItemテーブルから選択
        $outfit->tops = $request->input('tops');
        $outfit->outer = $request->input('outer');
        $outfit->bottoms = $request->input('bottoms');
        $outfit->shoes = $request->input('shoes');
        $outfit->save();

        return response()->json($outfit, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $outfit = Outfit::find($id);
        if (!$outfit) {
            return response()->json(['message' => 'お探しのコーディネートが見つかりません'], 404);
        }
        return response()->json($outfit, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = $this->validateRequest($request);

        $outfit = Outfit::find($id);

        if (auth()->user()->id !== $outfit->user_id) {
            return abort(403);
        }

        // ファイルが更新された場合のみバリデーションを無効にする
        if ($request->hasFile('file')) {
            $this->validateRequest($request);
        }
        // コーディネートした日付を選択する
        $outfit->outfit_date = $request->input('outfit_date');
        // 指定された日付に既に投稿があるか確認
        $validator->after(function ($validator) use ($outfit) {
            if (Outfit::onDate($outfit->outfit_date)->where('user_id', $outfit->user_id)->where('id', '!=', $outfit->id)->exists()) {
                $validator->errors()->add('outfit_date', '同じ日付に複数の投稿はできません。');
            }
        });

        $outfit = $this->createOrUpdateOutfit($outfit, $request);
        $outfit->save();

        return response()->json($outfit, 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $outfit = Outfit::find($id);

        if (!$outfit) {
            return response()->json(['error' => 'コーディネートが見つかりません'], 404);
        }

        if (!empty($outfit->file)) {
            $currentFile = public_path() . $outfit->file;

            if (file_exists($currentFile)) {
                if (!unlink($currentFile)) {
                    return response()->json(['error' => 'ファイルの削除に失敗しました'], 500);
                }
            }
        }

        $outfit->delete();

        return response()->json(['message' => 'コーディネートを削除しました'], 200);
    }
}
