// src/composables/useFileUploader.js
import { ref } from 'vue';

export function useFileUploader(options = {}) {
    const fileDisplay = ref('');
    const isValidFile = ref(null);

    // デフォルト許可拡張子（必要に応じて呼び出し元で上書き可能）
    const allowedExtensions = options.allowedExtensions || [
        'png',
        'jpg',
        'jpeg',
        'webp',
    ];

    /**
     * ファイルをアップロードして状態を返す
     * @param {Event} e - input[type=file] の change イベント
     * @param {Function} onFileSet - ファイルが有効だった時に呼び出すコールバック (file を渡す)
     */
    const getUploadedImage = (e, onFileSet) => {
        if (fileDisplay.value) {
            URL.revokeObjectURL(fileDisplay.value);
        }

        const file = e?.target?.files?.[0];
        if (!file) return;

        const extension = file.name.split('.').pop().toLowerCase();
        isValidFile.value = allowedExtensions.includes(extension);

        if (!isValidFile.value) return;

        fileDisplay.value = URL.createObjectURL(file);

        // 呼び出し元に file を渡す
        if (typeof onFileSet === 'function') {
            onFileSet(file);
        }

        e.target.value = ''; // input をリセット
    };

    return {
        fileDisplay,
        isValidFile,
        getUploadedImage,
    };
}
