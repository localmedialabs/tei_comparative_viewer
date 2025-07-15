<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import icon from "leaflet/dist/images/marker-icon.png"; // ピンのiconが表示されないバグの対応
import iconRetina from "leaflet/dist/images/marker-icon-2x.png"; // ピンのiconが表示されないバグの対応
import iconShadow from "leaflet/dist/images/marker-shadow.png"; // ピンのiconが表示されないバグの対応

// propsとしてcoordinatesを受け取る
const props = defineProps({
  coordinates: {
    type: Array,
    required: true,
  },
});

// 地図のインスタンスと表示フラグを定義
const map = ref(null);
const showPopup = ref(false); // ポップアップ表示フラグ
const showMarker = ref(false); // マーカー表示フラグ

// 地図の初期化関数
const initializeMap = (coordinates) => {
  if (!coordinates) return;

  // 座標と場所名を分解
  const [coords, locationName] = coordinates;
  const [lat, lng] = coords.split(" ").map(Number);

  // 既存の地図があれば一度削除
  if (map.value) {
    map.value.off(); // 地図のイベントリスナーを解除
    map.value.remove(); // 現在の地図を削除
  }

  // DOM 更新後に地図を初期化
  nextTick(() => {
    const mapContainer = document.getElementById("map");
    if (!mapContainer) {
      console.error("Map container not found");
      return;
    }

    // 地図の初期化
    map.value = L.map("map").setView([lat, lng], 7);

    // OpenStreetMapのタイルレイヤーを追加
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map.value);

    // ピンのiconが表示されないバグの対応
    const DefaultIcon = L.icon({
      iconUrl: icon,
      iconRetinaUrl: iconRetina,
      shadowUrl: iconShadow,
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34],
      tooltipAnchor: [16, -28],
      shadowSize: [41, 41],
    });
    L.Marker.prototype.options.icon = DefaultIcon;

    // マーカーの表示を条件付きで設定
    if (showMarker.value) {
      const marker = L.marker([lat, lng]).addTo(map.value);

      // showPopupがtrueの場合にポップアップを表示
      if (showPopup.value) {
        marker.bindPopup(locationName).openPopup();
      }
    }
  });
};

// 初期化時に地図を表示
onMounted(() => {
  // 初期座標が存在しない場合は東京駅を指定し、ポップアップやマーカーを表示しない
  if (props.coordinates.length === 0) {
    showPopup.value = false; // ポップアップを表示しない
    showMarker.value = false; // マーカーを表示しない
    initializeMap(["35.6813039 139.7644855", "東京駅"]);
  } else {
    showPopup.value = true; // ポップアップを表示
    showMarker.value = true; // マーカーを表示
    initializeMap(props.coordinates);
  }
});

// coordinatesが変更された際に再度地図を更新
watch(
  () => props.coordinates,
  (newCoordinates) => {
    showPopup.value = true; // 座標が更新された時にポップアップを表示
    showMarker.value = true; // 座標が更新された時にマーカーを表示
    initializeMap(newCoordinates);
  }
);
</script>
<template>
  <div id="map" style="height: 100%; width: 100%"></div>
</template>

<style scoped>
#map {
  height: 100%;
  width: 100%;
}
</style>
