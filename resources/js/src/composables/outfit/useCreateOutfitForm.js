import { ref, reactive } from 'vue';

export function useCreateOutfitForm(closeOverlay) {
    const form = reactive({
        file: null,
        description: null,
        outfit_date: '',
        season: null,
        items: [],
    });

    const fileDisplay = ref('');
    const isValidFile = ref(null);
    const error = ref({});

    // コーディネートに使用したアイテムのカテゴリー
    const itemTypes = {
        1: { key: 'outer', imgKey: 'outerImage', label: 'アウター' },
        2: { key: 'tops', imgKey: 'topsImage', label: 'トップス' },
        3: { key: 'bottoms', imgKey: 'bottomsImage', label: 'ボトムス' },
        4: { key: 'shoes', imgKey: 'shoesImage', label: 'シューズ' },
    };

    const createOutfit = async () => {
        Object.keys(error.value).forEach((key) => (error.value[key] = null));
        form.season = form.season === '' ? null : form.season;
        try {
            const payload = new FormData();

            payload.append('file', form.file);
            payload.append('description', form.description);
            payload.append('outfit_date', form.outfit_date);
            payload.append('season', form.season);
            payload.append('items', JSON.stringify(form.items));
            const response = await axios.post('/api/outfit', payload, {
                forceFormData: true,
                preserveScroll: true,
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            if (response.status === 200) {
                closeOverlay();
                window.dispatchEvent(new Event('outfit-created'));
            }
        } catch (errors) {
            if (errors.response && errors.response.data.errors) {
                Object.assign(error.value, errors.response.data.errors);
            }
        }
    };

    const getUploadedImage = (e) => {
        form.file = e.target.files[0];
        const extension = form.file.name.split('.').pop().toLowerCase(); // ファイル拡張子を小文字で取得

        isValidFile.value = ['png', 'jpg', 'jpeg'].includes(extension);
        if (!isValidFile.value) return;

        fileDisplay.value = URL.createObjectURL(form.file);
        setTimeout(() => {
            document
                .getElementById('TextAreaSection')
                .scrollIntoView({ behavior: 'smooth' });
        }, 300);
    };

    // コーディネートに使用したアイテムを選択する
    const handleItemSelected = (selectedItem, itemType) => {
        const itemInfo = itemTypes[itemType];
        if (!itemInfo) return;

        const role = itemType;
        form.items = form.items.filter((i) => i.role !== role);

        if (selectedItem) {
            form.items.push({
                item_id: selectedItem.id,
                role: role,
                file: selectedItem.file,
            });
        }
    };

    const resetForm = () => {
        Object.assign(form, {
            file: null,
            description: null,
            outfit_date: '',
            season: '',
            items: [],
        });
        fileDisplay.value = '';
    };

    return {
        form,
        fileDisplay,
        isValidFile,
        error,
        itemTypes,
        createOutfit,
        getUploadedImage,
        handleItemSelected,
        resetForm,
    };
}
