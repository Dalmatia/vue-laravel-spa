<?php

namespace App\Services;

use App\Models\Outfit;
use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Support\Facades\Auth;

class OutfitService
{
  private $fileService;

  public function __construct(FileService $fileService)
  {
    $this->fileService = $fileService;
  }

  public function createOrUpdateOutfit(Outfit $outfit, Request $request)
  {
    $outfit->user_id = Auth::id();
    if ($request->hasFile('file')) {
      $outfit = $this->handleFileUpload($outfit, $request);
    }
    $this->fillOutfitAttributes($outfit, $request);
    $outfit->save();

    $this->syncItemRelations($outfit, $request);

    $outfit->load('items');

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
  }

  private function syncItemRelations(Outfit $outfit, Request $request)
  {
    $items = json_decode($request->input('items'), true) ?? [];

    $syncData = [];

    foreach ($items as $item) {
      if (!isset($item['item_id'])) continue;

      $syncData[$item['item_id']] = [
        'role' => $item['role'] ?? null
      ];
    }

    $outfit->items()->sync($syncData);
  }

  private function normalizeNull($value)
  {
    return $value === 'null' ? null : $value;
  }
}
