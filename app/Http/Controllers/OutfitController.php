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
    private $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    private function validateRequest(Request $request, $outfitId = null)
    {
        $rules = [
            'file' => 'required',
            'description' => 'nullable',
            'outfit_date' => 'required',
            'season' => 'nullable',
            'tops' => 'nullable',
            'outer' => 'nullable',
            'bottoms' => 'nullable',
            'shoes' => 'nullable'
        ];

        // ファイルがアップロードされている場合のみバリデーションを追加
        if ($request->hasFile('file')) {
            $rules['file'] = 'mimes:jpg,jpeg,png';
        }

        $validator = Validator::make($request->all(), $rules);
        $this->validateUniqueOutfitDate($validator, $request, $outfitId); // バリデーションを別メソッドに分ける

        return $validator;
    }

    // 同じ日付の投稿が存在するか確認
    private function validateUniqueOutfitDate($validator, Request $request, $outfitId)
    {
        $validator->after(function ($validator) use ($request, $outfitId) {
            $existingOutfit = Outfit::where('outfit_date', $request->input('outfit_date'))
                ->where('user_id', auth()->id())
                ->when($outfitId, function ($query) use ($outfitId) {
                    return $query->where('id', '!=', $outfitId);
                })
                ->exists();

            if ($existingOutfit) {
                $validator->errors()->add('outfit_date', '同じ日付に複数の投稿はできません。');
            }
        });
    }

    private function createOrUpdateOutfit(Outfit $outfit, Request $request)
    {
        $outfit->user_id = auth()->user()->id;
        if ($request->hasFile('file')) {
            $outfit = $this->fileService->updateFile($outfit, $request, 'outfit');
        }
        $description = $request->filled('description') ? $request->description : null;
        $outfit->description = $description === 'null' ? null : $description;
        $outfit->outfit_date = $request->input('outfit_date');
        $season = $request->filled('season') ? $request->season : null;
        $outfit->season = $season === 'null' ? null : $season;

        // アイテムIDを収集
        $itemIds = collect(['tops', 'outer', 'bottoms', 'shoes'])
            ->map(fn($item) => $request->filled($item) && $request->$item !== 'null' ? $request->$item : null)
            ->filter()
            ->unique()
            ->toArray();

        foreach (['tops', 'outer', 'bottoms', 'shoes'] as $item) {
            $outfit->$item = $request->filled($item) && $request->$item !== 'null' ? $request->$item : null;
        }

        $outfit->save();
        $outfit->items()->sync($itemIds);

        return $outfit;
    }

    public function index(Request $request)
    {
        $query = Outfit::query();

        // フィルタリング条件を取得
        $filters = [
            'main_category' => $request->query('mainCategory'),
            'sub_category' => $request->query('subCategory'),
            'color' => $request->query('color'),
            'season' => $request->query('season')
        ];

        // フィルタリング条件に応じてクエリを構築
        foreach ($filters as $filter => $value) {
            if ($value) {
                if ($filter === 'season') {
                    $query->where($filter, $value);
                } else {
                    $query->whereHas('items', function ($query) use ($filter, $value) {
                        $query->where($filter, $value);
                    });
                }
            }
        }
        $outfits = $query->orderBy('outfit_date', 'desc')->get();

        // コーディネートを取得し、ユーザー情報も取得
        return response(['outfits' => new AllOutfitsCollection($outfits), 'users' => User::all()]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $outfit = new Outfit();
        $outfit = $this->createOrUpdateOutfit($outfit, $request);
        return response()->json($outfit, 200);
    }

    public function show($id)
    {
        $outfit = Outfit::find($id);
        if (!$outfit) {
            return response()->json(['message' => 'お探しのコーディネートが見つかりません'], 404);
        }
        return response()->json($outfit, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateRequest($request, $id);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $outfit = Outfit::find($id);
        if (!$outfit) {
            return response()->json(['error' => 'コーディネートが見つかりません'], 404);
        }

        if (auth()->user()->id !== $outfit->user_id) {
            return abort(403);
        }

        $outfit = $this->createOrUpdateOutfit($outfit, $request);
        return response()->json($outfit, 200);
    }

    public function destroy($id)
    {
        $outfit = Outfit::find($id);
        if (!$outfit) {
            return response()->json(['error' => 'コーディネートが見つかりません'], 404);
        }

        if ($outfit->file && file_exists(public_path($outfit->file))) {
            unlink(public_path($outfit->file));
        }

        $outfit->delete();
        return response()->json(['message' => 'コーディネートを削除しました'], 200);
    }
}
