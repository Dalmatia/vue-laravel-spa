import { ref, watch, onMounted, computed } from 'vue';

export function useEditOutfitForm(props, emit) {
    const editForm = ref({ ...props.editOutfit });

    const outer = ref({});
    const tops = ref({});
    const bottoms = ref({});
    const shoes = ref({});
    const seasons = ref([]);

    const itemRefs = computed(() => ({
        outer: outer.value,
        tops: tops.value,
        bottoms: bottoms.value,
        shoes: shoes.value,
    }));

    const isValidFile = ref(null);
    const fileDisplay = ref('');

    const error = ref({
        file: null,
        description: null,
        outfit_date: '',
        season: '',
        tops: '',
        outer: '',
        bottoms: '',
        shoes: '',
    });

    // コーディネートに使用したアイテムのカテゴリー
    const itemTypes = {
        1: { key: 'outer', imgKey: 'outerImage', label: 'アウター' },
        2: { key: 'tops', imgKey: 'topsImage', label: 'トップス' },
        3: { key: 'bottoms', imgKey: 'bottomsImage', label: 'ボトムス' },
        4: { key: 'shoes', imgKey: 'shoesImage', label: 'シューズ' },
    };

    const itemTypeArray = Object.entries(itemTypes).map(([type, info]) => ({
        type: Number(type),
        ...info,
    }));

    // 投稿時に選択したアイテムの表示
    const fetchItem = async () => {
        try {
            const fetchItemData = async (itemId, item) => {
                const response = await axios.get(`/api/items/${itemId}`);
                item.value = response.data;
            };

            const fetchPromises = [];

            if (editForm.value.tops) {
                fetchPromises.push(fetchItemData(editForm.value.tops, tops));
            }

            if (editForm.value.outer) {
                fetchPromises.push(fetchItemData(editForm.value.outer, outer));
            }

            if (editForm.value.bottoms) {
                fetchPromises.push(
                    fetchItemData(editForm.value.bottoms, bottoms)
                );
            }

            if (editForm.value.shoes) {
                fetchPromises.push(fetchItemData(editForm.value.shoes, shoes));
            }

            await Promise.all(fetchPromises);
        } catch (error) {
            console.error('データの取得に失敗しました:', error);
        }
    };

    const getSeason = async () => {
        try {
            const response = await axios.get('/api/enums');
            seasons.value = response.data.seasons;
        } catch (error) {
            console.error('Enum データの取得に失敗しました', error);
        }
    };

    // コーディネートの編集機能
    const outfitUpdate = async () => {
        Object.keys(error.value).forEach((key) => (error.value[key] = null));
        editForm.value.season =
            editForm.value.season === '' ? null : editForm.value.season;

        const formData = new FormData();
        if (editForm.value.file instanceof File) {
            formData.append('file', editForm.value.file);
        }
        formData.append('description', editForm.value.description);
        formData.append('outfit_date', editForm.value.outfit_date);
        formData.append('season', editForm.value.season);
        formData.append('tops', editForm.value.tops);
        formData.append('outer', editForm.value.outer);
        formData.append('bottoms', editForm.value.bottoms);
        formData.append('shoes', editForm.value.shoes);

        try {
            const response = await axios.post(
                `/api/outfit/${editForm.value.id}`,
                formData,
                {
                    forceFormData: true,
                    preserveScroll: true,
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );
            if (response.status === 200) {
                emit('closeOverlay');
                window.dispatchEvent(new Event('outfit-updated'));
            }
        } catch (errors) {
            if (errors.response?.data?.errors) {
                Object.assign(error.value, errors.response.data.errors);
            }
        }
    };

    // ファイルアップロード
    const getUploadedImage = (e) => {
        const file = e?.target?.files?.[0];
        if (!file) return;

        editForm.value.file = file;

        const extension = file.name.split('.').pop().toLowerCase(); // ファイル拡張子を小文字で取得
        isValidFile.value = ['png', 'jpg', 'jpeg', 'webp'].includes(extension);
        if (!isValidFile.value) return;

        fileDisplay.value = URL.createObjectURL(file);
        setTimeout(() => {
            document
                .getElementById('TextAreaSection')
                .scrollIntoView({ behavior: 'smooth' });
        }, 300);
    };

    // コーディネートに使用したアイテムを選択する
    const handleItemSelected = (selectedItem, selectedItemType) => {
        const itemType = itemTypes[selectedItemType];
        if (itemType) {
            editForm.value[itemType.key] = selectedItem
                ? selectedItem.id
                : null;
            editForm.value[itemType.imgKey] = selectedItem
                ? selectedItem.file
                : null;
            itemRefs.value[itemType.key] = selectedItem ? selectedItem : {};
        }
    };

    // 内容が変更されなかった時にeditFormを初期化
    watch(
        () => props.editOutfit,
        (newEditOutfit) => {
            editForm.value = JSON.parse(JSON.stringify(newEditOutfit));
            fileDisplay.value = '';
            fetchItem(); // アイテムの再取得
        },
        { immediate: true } // 初回マウント時にも実行
    );

    onMounted(async () => {
        await Promise.all([fetchItem(), getSeason()]);
    });

    return {
        editForm,
        itemRefs,
        seasons,
        error,
        itemTypeArray,
        fileDisplay,
        isValidFile,
        getUploadedImage,
        outfitUpdate,
        handleItemSelected,
    };
}
