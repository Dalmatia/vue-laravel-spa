import { ref } from 'vue';

export function useOutfitImage() {
    const file = ref(null);
    const fileDisplay = ref('');
    const isValidFile = ref(null);

    const getUploadedImage = (e) => {
        const selectedFile = e?.target?.files?.[0];
        if (!selectedFile) return;

        file.value = selectedFile;

        const extension = selectedFile.name.split('.').pop().toLowerCase(); // ファイル拡張子を小文字で取得

        isValidFile.value = ['png', 'jpg', 'jpeg', 'webp'].includes(extension);

        if (!isValidFile.value) return;

        fileDisplay.value = URL.createObjectURL(selectedFile);
    };

    return {
        file,
        fileDisplay,
        isValidFile,
        getUploadedImage,
    };
}
