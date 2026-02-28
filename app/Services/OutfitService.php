<?php

namespace App\Services;

use App\Models\Outfit;
use Illuminate\Http\Request;
use App\Services\FileService;

class OutfitService
{
  private $fileService;

  public function __construct(FileService $fileService)
  {
    $this->fileService = $fileService;
  }

  public function createOrUpdateOutfit(Outfit $outfit, Request $request)
  {
    $outfit->user_id = auth()->user()->id;
    if ($request->hasFile('file')) {
      $outfit = $this->handleFileUpload($outfit, $request);
    }
    $this->fillOutfitAttributes($outfit, $request);
    $outfit->save();

    $this->syncItemRelations($outfit, $request);

    return $outfit;
  }

  private function handleFileUpload(Outfit $outfit, Request $request)
  {
    return $this->fileService->updateFile($outfit, $request, 'outfit');
  }

  private function fillOutfitAttributes(Outfit $outfit, Request $request)
  {
    $outfit->description = $this->normalizeNull($request->input('description'));
    $outfit->outfit_date = $request->input('outfit_date');
    $outfit->season = $this->normalizeNull($request->input('season'));

    foreach (['tops', 'outer', 'bottoms', 'shoes'] as $item) {
      $outfit->$item = $this->normalizeNull($request->input($item));
    }
  }

  private function syncItemRelations(Outfit $outfit, Request $request)
  {
    $itemIds = collect(['tops', 'outer', 'bottoms', 'shoes'])
      ->map(fn($item) => $this->normalizeNull($request->input($item)))
      ->filter()
      ->unique()
      ->toArray();

    $outfit->items()->sync($itemIds);
  }

  private function normalizeNull($value)
  {
    return $value === 'null' ? null : $value;
  }
}
