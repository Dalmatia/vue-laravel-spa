import { ref } from 'vue';

export function useOutfitItemForm(initialItems = []) {
    const items = ref([]);

    const normalizeItems = (rawItems = []) => {
        items.value = rawItems.map((item) => ({
            item_id: item.item_id ?? item.id,
            role: item.role ?? item.pivot?.role,
            file: item.file,
        }));
    };

    // コーディネートに使用したアイテムを選択する
    const handleItemSelected = (selectedItem, role) => {
        items.value = items.value.filter((i) => i.role !== role);

        if (selectedItem) {
            items.value.push({
                item_id: selectedItem.id,
                role: role,
                file: selectedItem.file,
            });
        }
    };

    const getItemByRole = (role) => {
        return items.value.find((i) => i.role === role);
    };

    return {
        items,
        normalizeItems,
        handleItemSelected,
        getItemByRole,
    };
}
