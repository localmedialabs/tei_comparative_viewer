<script setup>
import { ref, onMounted, watch } from "vue";
import OpenSeadragon from "openseadragon";

const props = defineProps({
  manifest_data: {
    type: Array,
    required: true,
  },
  page: String,
  selectZone: Object,
  hoverZone: Object,
  writeMode: String,
});
// console.warnを無効化
console.warn = function () {};
// インスタンス定義
const viewer = ref("");
// 現在のページ
const nowPage = ref(1);
// 元画像のサイズ
let imageWidth = props.manifest_data[0].width; // 初期画像全体の幅
let imageHeight = props.manifest_data[0].height; // 初期画像全体の高さ

// ページ数取得
const pageCount = props.manifest_data.length;

// DOM描画後に処理
onMounted(() => {
  viewer.value = OpenSeadragon({
    id: "openseadragon-viewer",
    tileSources: props.manifest_data[0].info,
    prefixUrl: "https://openseadragon.github.io/openseadragon/images/",
    showNavigator: false, // ミニマップ非表示
    showZoomControl: false, // ズームボタンを非表示
    showHomeControl: false, // ホームボタンを非表示
    showFullPageControl: false, // フルスクリーンボタンを非表示
    controlsFadeLength: 0, // その他のコントロールを非表示
  });
  document.querySelector(".openseadragon-canvas").style.outline = "none"; // Chromiumボーダーライン非表示
});

// 指定のページに遷移する関数
const changePage = (page, zone) => {
  nowPage.value = page;
  viewer.value.open(props.manifest_data[page - 1].info); // インデックス指定なのでpage-1
  // 新しい画像の幅と高さを取得
  imageWidth = props.manifest_data[page - 1].width;
  imageHeight = props.manifest_data[page - 1].height;
  // 指定のエリアにズーム
  viewer.value.addHandler("open", () => {
    if (zone) {
      zoomPage(zone);
    } else {
      viewer.value.viewport.goHome();
    }
  });
};

// 前のページへ遷移する関数
const prevPage = () => {
  if (nowPage.value > 1) {
    changePage(nowPage.value - 1, null);
  }
};

// 次のページへ遷移する関数
const nextPage = () => {
  if (nowPage.value < pageCount) {
    changePage(nowPage.value + 1, null);
  }
};

// 指定座標にズームする関数
const zoomPage = (selectZone) => {
  // テキストのクリックしたエリアから正規化座標に変換
  const normalizedX = selectZone.ulx / imageWidth;
  const normalizedY = (selectZone.uly * (imageHeight / imageWidth)) / imageHeight;
  const normalizedWidth = Math.abs(selectZone.lrx - selectZone.ulx) / imageWidth;
  const normalizedHeight = (Math.abs(selectZone.uly - selectZone.lry) * (imageHeight / imageWidth)) / imageHeight;

  // 正規化された範囲を指定してズーム
  viewer.value.viewport.fitBounds(
    new OpenSeadragon.Rect(
      normalizedX, // 左上のX座標
      normalizedY - 0.075, // 左上のY座標
      normalizedWidth, // 幅
      normalizedHeight + 0.15 // 高さ
    ),
    false // アニメーション（falseで有効）
  );
};

// 指定座標にオーバーレイを表示する関数
const addOverlay = (hoverZone) => {
  // テキストのホバーゾーンから正規化座標に変換
  const normalizedX = hoverZone.ulx / imageWidth;
  const normalizedY = (hoverZone.uly * (imageHeight / imageWidth)) / imageHeight;
  const normalizedWidth = Math.abs(hoverZone.lrx - hoverZone.ulx) / imageWidth;
  const normalizedHeight = (Math.abs(hoverZone.uly - hoverZone.lry) * (imageHeight / imageWidth)) / imageHeight;
  // オーバーレイを複数表示させない
  if (viewer.value.currentOverlays.length === 0) {
    // 重ねて表示するオーバーレイ
    let overlayElement = document.createElement("div");
    overlayElement.id = "hover-zone";
    overlayElement.style.background = "rgba(144, 249, 255, 0.3)";
    // オーバーレイ追加
    viewer.value.addOverlay({
      element: overlayElement,
      location: new OpenSeadragon.Rect(
        normalizedX, // 左上のX座標
        normalizedY, // 左上のY座標
        normalizedWidth, // 幅
        normalizedHeight // 高さ
      ),
    });
  }
};

// 表示中のオーバーレイを削除する関数
const clearOverlay = () => {
  viewer.value.removeOverlay("hover-zone");
};

// 全画面表示を切り替える関数
const changeFullScreen = () => {
  const isFullScreen = viewer.value.isFullPage(); // 現在の全画面状態
  viewer.value.setFullScreen(!isFullScreen); // 切り替え
  // ボタンの切り替え
  const fullScreenButton = document.getElementById("fullscreen-button");
  if (isFullScreen) {
    fullScreenButton.classList.remove("fa-compress");
    fullScreenButton.classList.add("fa-expand");
  } else {
    fullScreenButton.classList.remove("fa-expand");
    fullScreenButton.classList.add("fa-compress");
  }

  const OverlayContainer = document.getElementById("custom-overlay_container");
  if (isFullScreen) {
    OverlayContainer.classList.remove("fs-3");
    OverlayContainer.classList.add("fs-5");
  } else {
    OverlayContainer.classList.add("fs-3");
    OverlayContainer.classList.remove("fs-5");
  }
};

// ズームイン
const zoomIn = () => {
  const center = viewer.value.viewport.getCenter(); // ビューアの現在の中心座標を取得
  const zoomLevel = viewer.value.viewport.getZoom() * 1.4; // ズームレベルを2倍にする（調整可能）
  // 中心を基準にズームを実行
  viewer.value.viewport.zoomTo(zoomLevel, center, false);
};

// ズームアウト
const zoomOut = () => {
  const center = viewer.value.viewport.getCenter(); // ビューアの現在の中心座標を取得
  const zoomLevel = viewer.value.viewport.getZoom() * 0.7; // ズームレベルを2倍にする（調整可能）
  // 中心を基準にズームを実行
  viewer.value.viewport.zoomTo(zoomLevel, center, false);
};

// テキストのクリックを監視
watch(
  () => ({ page: props.page, selectZone: props.selectZone }),
  ({ page, selectZone }) => {
    if (page != null && page != nowPage.value && pageCount !== 1) {
      changePage(Number(page), selectZone);
    } else {
      zoomPage(selectZone);
    }
  }
);

// テキストのホバーを監視
watch(
  () => ({ hoverZone: props.hoverZone }),
  ({ hoverZone }) => {
    if (pageCount === 1 && hoverZone !== null) {
      addOverlay(hoverZone);
    } else if (pageCount === 1 && hoverZone === null) {
      clearOverlay();
    }
  }
);
</script>

<template>
  <div class="viewer-container">
    <div id="openseadragon-viewer" class="openseadragon-viewer">
      <!-- ボタン等の固定オーバーレイ要素 -->
      <div id="custom-overlay">
        <div class="d-flex align-items-center justify-content-around px-1 h-100 fs-5" id="custom-overlay_container">
          <div class="d-flex gap-2 gap-xl-3">
            <div class="button-container route-button rounded-circle border border-dark">
              <button @click="zoomOut">
                <i class="fa-solid fa-magnifying-glass-minus p-2"></i>
              </button>
            </div>
            <div class="button-container route-button rounded-circle border border-dark">
              <button @click="zoomIn">
                <i class="fa-solid fa-magnifying-glass-plus p-2"></i>
              </button>
            </div>
            <div class="button-container route-button rounded-circle border border-dark ms-2">
              <button @click="changeFullScreen">
                <i id="fullscreen-button" class="fa-solid fa-expand p-2"></i>
              </button>
            </div>
          </div>
          <div class="d-flex gap-2 gap-xl-3 gap-xxl-4 align-items-center fw-bold" id="custom-overlay_page-nav">
            <template v-if="writeMode === 'horizontal'">
              <div class="prev-button route-button">
                <button v-if="nowPage != 1" @click="prevPage"><i class="fa-solid fa-chevron-left"></i><span class="button-text ms-1">前へ</span></button>
              </div>
              <div class="page-count fs-5 d-flex justify-content-center">
                <p class="mb-0 small">{{ nowPage }} /{{ pageCount }}</p>
              </div>
              <div class="next-button route-button">
                <button v-if="nowPage != pageCount" @click="nextPage"><span class="button-text me-1">次へ</span><i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </template>
            <template v-else>
              <div class="next-button route-button">
                <button v-if="nowPage != pageCount" @click="nextPage"><i class="fa-solid fa-chevron-left"></i> <span class="button-text me-1">次へ</span></button>
              </div>
              <div class="page-count fs-5 d-flex justify-content-center">
                <p class="mb-0 small">{{ nowPage }} /{{ pageCount }}</p>
              </div>
              <div class="prev-button route-button">
                <button v-if="nowPage != 1" @click="prevPage"><span class="button-text me-1">前へ</span><i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.viewer-container {
  position: relative;
  height: 100%;
}
.openseadragon-viewer {
  width: 100%;
  height: 100%;
  background-color: black;
  container-type: inline-size; /* 親要素の幅を検出 */
  container-name: parent-container;
}
#custom-overlay {
  position: absolute;
  width: 100%;
  height: 10%;
  min-height: 55px;
  bottom: 0;
  left: 0px;
  background-color: rgba(255, 255, 255, 0.8);
  border: 1.5px solid #212529;
  z-index: 999;
}
.route-button {
  button {
    all: unset; /* すべてのデフォルトスタイルをリセット */
    cursor: pointer; /* ボタンらしさを維持するためにカーソルをポインタに */
    color: inherit; /* 親要素のテキスト色を継承 */
    font: inherit; /* 親要素のフォントスタイルを継承 */
  }
}
.button-container {
  i {
    transition: transform 0.3s;
  }
  &:hover {
    i {
      transform: scale(1.15); /* 拡大 */
    }
  }
}
.next-button,
.prev-button {
  transition: color 0.3s;
  &:hover {
    color: #85023c;
  }
}

@container parent-container (max-width: 250px) {
  #custom-overlay {
    height: 15%;
    top: 85%;
  }
  .button-text {
    display: none;
  }
}
</style>
