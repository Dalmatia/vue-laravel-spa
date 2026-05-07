export function createQueryKey(query) {
    return JSON.stringify({
        gender: query.gender ?? null,
        mainCategory: query.mainCategory ?? null,
        subCategory: query.subCategory ?? null,
        color: query.color ?? null,
        season: query.season ?? null,
        sort: query.sort ?? 'popular',
    });
}
