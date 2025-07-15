<script setup>
import { watch } from "vue";

const props = defineProps({
  people: Object, // 人名リスト
  places: Object, // 地名リスト
  displayList: String, // 人名・地名リストの選択表示箇所
});

// 親コンポーネントに情報送信
const emit = defineEmits(["updateListId", "updateCoordinates"]);

const sendListId = (listId) => {
  emit("updateListId", listId);
};

// 地図に表示の座標を更新
const sendCoordinates = (geoString, locationName) => {
  if (geoString) {
    emit("updateCoordinates", [geoString, locationName]);
  }
};

// 指定箇所にスクロールし、クリックイベントを発火
const scrollToSection = (id) => {
  const section = document.getElementById(id);
  if (section) {
    section.scrollIntoView({ behavior: "auto" });

    const list = document.getElementById(id + "_list");
    if (list) {
      if (list.classList.contains("show")) {
        if (section.classList.contains("list-place")) {
          // 地名かつリストが開いている状態の時、座標送信のみ
          const place = props.places[id];
          if (place && place.geo) {
            // 地名の座標がある場合、地図に表示
            sendCoordinates(place.geo, id);
          }
        }
      } else {
        // リストが閉じている場合、クリックイベントを発火
        const spans = section.getElementsByTagName("span");
        if (spans.length > 0) {
          // クリックイベント発火
          spans[0].click();
        }
      }
    }
  }
};

// テキストのホバーによるidの送信を監視
watch(
  () => props.displayList,
  (id) => {
    scrollToSection(id);
  }
);
</script>
<template>
  <h5 class="mt-3 ms-2 fw-bold">人名</h5>
  <div class="accordion accordion-flush">
    <div v-for="(person, key) in people" class="accordion-item list-person mx-2 my-1 bg-white border border-secondary-subtle" :id="key">
      <h3 class="accordion-header border-bottom">
        <button class="accordion-button collapsed bg-white py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" :data-bs-target="`#${key}_list`" aria-expanded="true" :aria-controls="`${key}_list`">
          <span class="flex-fill">{{ key }}</span>
          <span class="badge bg-primary rounded-pill float-end mx-2">{{ person.names.length }}</span>
        </button>
      </h3>
      <div :id="`${key}_list`" class="accordion-collapse-top collapse">
        <div class="accordion-body p-2">
          <ul class="list-group my-2 rounded-0">
            <li v-for="(name, index) in person.names" class="list-group-item" :list_id="`${key}_${index + 1}`" @click="sendListId(`${key}_${index + 1}`)">
              <!-- ルビ付きのHTML文字列の場合がるためv-htmlで表示 -->
              <span v-html="name"></span>
            </li>
          </ul>
          <span v-if="person.note" class="list-note d-block ms-2 mt-1">{{ person.note }}</span>
          <span v-if="person.idno" class="list-idno d-block ms-2 mt-1">
            参考：<a target="_blank" rel="noopener noreferrer" :href="person.idno">{{ person.idno }}</a>
          </span>
        </div>
      </div>
    </div>
  </div>

  <h5 class="mt-3 ms-2 fw-bold">地名</h5>

  <div class="accordion accordion-flush">
    <div v-for="(place, key) in places" class="accordion-item list-place mx-2 my-1 bg-white border border-secondary-subtle" :id="key">
      <h3 class="accordion-header border-bottom">
        <button class="accordion-button collapsed bg-white py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" :data-bs-target="`#${key}_list`" aria-expanded="true" :aria-controls="`${key}_list`">
          <span class="flex-fill" @click="sendCoordinates(place.geo, key)">{{ key }}<i v-if="place.geo" style="cursor: pointer" class="ms-2 fa-solid fa-location-dot map-pin"></i></span>
          <span class="badge bg-primary rounded-pill float-end mx-2">{{ place.names.length }}</span>
        </button>
      </h3>
      <div :id="`${key}_list`" class="accordion-collapse-top collapse">
        <div class="accordion-body p-2">
          <ul class="list-group my-2 rounded-0">
            <li v-for="(name, index) in place.names" class="list-group-item" :list_id="`${key}_${index + 1}`" @click="sendListId(`${key}_${index + 1}`)">
              <!-- ルビ付きのHTML文字列の場合がるためv-htmlで表示 -->
              <span v-html="name"></span>
            </li>
          </ul>
          <span v-if="place.note" class="list-note d-block ms-2 mt-1">{{ place.note }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.map-pin {
  color: #3b87ba;
}
.accordion-button {
  box-shadow: none;
}
.list-group-item {
  &:hover {
    background-color: #eee;
  }
}
</style>
