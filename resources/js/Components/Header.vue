<script setup>
import { ref, watch } from "vue";
const props = defineProps({
  title: String, // TEIに記載のタイトル
  selectComponents: Object, // 表示コンポーネント
  tei_url: String, // TEIファイルのURL
});
// イベントの定義
const emit = defineEmits(["update-selectComponents"]);
const localSelectComponent = ref({ ...props.selectComponents });
// propsの変更を監視してローカル設定を更新
watch(
  () => props.selectComponents,
  (newSelectComponent) => {
    localSelectComponent.value = { ...newSelectComponent };
  }
);
// 表示コンポーネント変更
const toggleSelectComponent = (key) => {
  localSelectComponent.value[key] = !localSelectComponent.value[key]; // 値を切り替え
  emit("update-selectComponents", localSelectComponent.value); // 親に変更を通知
};
// コンポーネント名を表示用の日本語へ変換する関数
const changeJapanese = (componentName) => {
  const components = {
    Transcription: "翻刻",
    Transliteration: "読み下し",
    ModernTranslation: "現代語訳",
    Osd: "画像",
    Audio: "音声",
    About: "概要",
    Bibliography: "書誌事項",
    Place: "地図情報",
    List: "人名・地名リスト",
  };
  return components[componentName];
};
</script>
<template>
  <div class="tei-header d-flex justify-content-between px-3 gap-4">
    <div class="h-100 d-flex align-items-center">
      <h5 class="mb-0 tei-header-title">{{ title }}</h5>
    </div>
    <div class="buttons d-flex gap-3">
      <a type="button" class="d-block download-button my-auto py-1 px-3" :href="tei_url" :download="title">ダウンロード<i class="fa-solid fa-download ms-2"></i></a>
      <div class="dropdown my-auto">
        <button type="button" class="dropdown-button py-1 px-3" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"> 表示選択<i class="fa-solid fa-caret-down ms-2"></i> </button>
        <div class="dropdown-menu p-2">
          <div v-for="(value, key) in localSelectComponent" :key="key" class="dropdown-item">
            <label class="checkbox w-100">
              <input type="checkbox" :checked="value" @change="toggleSelectComponent(key)" />
              {{ changeJapanese(key) }}
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.tei-header {
  background-color: rgb(240, 240, 240);
}
.tei-header-title {
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2; /* 行数を指定 */
}
.buttons {
  white-space: nowrap;
}
.download-button {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  border: none;
  border-radius: 5px;
  border-bottom: 2px solid #d0d0d0;
  background-color: #fff;
  color: #333;
  font-size: 1em;
  cursor: pointer;
  text-decoration: none;
}
.dropdown-button {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  border: none;
  border-radius: 5px;
  border-bottom: 2px solid #d0d0d0;
  background-color: #fff;
  color: #333;
  font-size: 1em;
  cursor: pointer;
}
.dropdown-menu {
  width: 200px;
  border: none;
  border-radius: 5px;
  box-shadow: 6px 6px 6px 0px rgba(0, 0, 0, 0.45);
  .dropdown-item {
    &:not(:last-child) {
      margin-bottom: 0.7rem;
    }
  }
}
</style>
