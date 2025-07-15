<script setup>
import { ref } from "vue";
import Modal from "@/Components/Modal.vue";
const props = defineProps({
  bibliography: Object, // TEIに記載の書誌情報
});
// モーダルの表示状態を管理
const isModalVisible = ref(false);
// モーダルを開く
const openModal = () => {
  isModalVisible.value = true;
};
// モーダルを閉じる
const closeModal = () => {
  isModalVisible.value = false;
};
</script>
<template>
  <div class="ms-3 mt-3">
    <p class="mb-2"><span class="label">【タイトル】</span>{{ bibliography.title }}</p>
    <p v-for="author in bibliography.authors" class="mb-1"><span class="label">【著者】</span>{{ author }}</p>
  </div>

  <div class="d-flex justify-content-end modal-button">
    <a class="me-2 text-primary" @click="openModal">全て表示<i class="fa-solid fa-chevron-down ms-1"></i></a>
  </div>
  <!-- モーダルウィンドウの内容 -->
  <Modal :isVisible="isModalVisible" :modalName="'書誌事項'" @close="closeModal">
    <template #header>
      <h2 class="fs-4 mb-0">{{ bibliography.title }}</h2> </template
    ><span class="label">【】</span>
    <template #body>
      <p class="mb-1" v-if="bibliography.ms_titles.caption"><span class="label">【内題】</span>{{ bibliography.ms_titles.caption }}</p>
      <p class="mb-1" v-if="bibliography.ms_titles.cover"><span class="label">【外題/表題】</span>{{ bibliography.ms_titles.cover }}</p>
      <p class="mb-1" v-if="bibliography.ms_titles.alt"><span class="label">【その他名称】</span>{{ bibliography.ms_titles.alt }}</p>
      <p v-for="author in bibliography.authors" class="mb-1"><span class="label">【著者】</span>{{ author }}</p>
      <p v-for="editor in bibliography.editors" class="mb-1"><span class="label">【編者/版元】</span>{{ editor }}</p>
      <p class="mb-1" v-if="bibliography.date"><span class="label">【成立時代】</span>{{ bibliography.date }}</p>
      <p class="mb-1" v-if="bibliography.origDate"><span class="label">【完成年】</span>{{ bibliography.origDate }}</p>
      <p v-for="sentPersName in bibliography.sentPersNames" class="mb-1"><span class="label">【差出人】</span>{{ sentPersName }}</p>
      <p v-for="receivedPersName in bibliography.receivedPersNames" class="mb-1"><span class="label">【名宛人】</span>{{ receivedPersName }}</p>
      <p class="mb-1" v-if="bibliography.sentDate"><span class="label">【送信年月日】</span>{{ bibliography.sentDate }}</p>
      <p class="mb-1" v-if="bibliography.receivedDate"><span class="label">【受信年月日】</span>{{ bibliography.receivedDate }}</p>
      <p class="mb-1" v-if="bibliography.colophon"><span class="label">【奥書】</span>{{ bibliography.colophon }}</p>
      <p v-for="ab in bibliography.abs" class="mb-1"><span class="label">【識語】</span>{{ ab }}</p>
      <p class="mb-1" v-if="bibliography.bindingDesc"><span class="label">【形態/品質/形状】</span>{{ bibliography.bindingDesc }}</p>
      <p class="mb-1" v-if="bibliography.support"><span class="label">【料紙】</span>{{ bibliography.support }}</p>
      <p class="mb-1" v-if="bibliography.biblScope"><span class="label">【員数】</span>{{ bibliography.biblScope }}</p>
      <p class="mb-1" v-if="bibliography.extent"><span class="label">【丁数/枚数】</span>{{ bibliography.extent }}</p>
      <p class="mb-1" v-if="bibliography.unit || bibliography.height || bibliography.width || bibliography.depth">
        <span class="label">【法量】</span>
        <span v-if="bibliography.height">{{ bibliography.height }}</span>
        <span v-else>?</span>
        <span class="mx-1">×</span>
        <span v-if="bibliography.width">{{ bibliography.width }}</span>
        <span v-else>?</span>
        <span class="mx-1">×</span>
        <span v-if="bibliography.depth">{{ bibliography.depth }}</span>
        <span v-else>?</span>
        <span class="ms-1" v-if="bibliography.unit">{{ bibliography.unit }}</span>
      </p>
      <p class="mb-1" v-if="bibliography.decoNote"><span class="label">【付属品】</span>{{ bibliography.decoNote }}</p>
      <p v-for="sealDesc in bibliography.sealDescs" class="mb-1"><span class="label">【蔵書印等】</span>{{ sealDesc }}</p>
      <p v-for="acquisition in bibliography.acquisitions" class="mb-1"><span class="label">【旧蔵者】</span>{{ acquisition }}</p>
      <p v-for="provenance in bibliography.provenances" class="mb-1"><span class="label">【入手経緯】</span>{{ provenance }}</p>
      <p class="mb-1" v-if="bibliography.institution"><span class="label">【所蔵者】</span>{{ bibliography.institution }}</p>
      <p class="mb-1" v-if="bibliography.repository">
        <span class="label">【貴重資料デジタルアーカイブ】</span><a target="_blank" rel="noopener noreferrer" :href="bibliography.repository">{{ bibliography.repository }}</a>
      </p>
      <p class="mb-1" v-if="bibliography.idno">
        <span class="label">【URI】</span>
        <a v-if="bibliography.idno_type === 'URI'" target="_blank" rel="noopener noreferrer" :href="bibliography.idno"> {{ bibliography.idno }}</a>
        <span v-else>{{ bibliography.idno }}</span>
      </p>
      <p v-for="collection in bibliography.collections" class="mb-1"><span class="label">【コレクション】</span>{{ collection }}</p>
      <p v-for="term in bibliography.terms" class="mb-3"><span class="label">【主題】</span>{{ term }}</p>
      <p v-for="(name, key) in bibliography.names" class="mb-1">
        <span class="label">【{{ key }}】</span>{{ name }}
      </p>
      <p class="mb-1" v-if="bibliography.publish_date"><span class="label">【公開日】</span>{{ bibliography.publish_date }}</p>
      <!-- 以降記載場所の指示なし -->
      <p class="mb-1" v-if="bibliography.publisher"><span class="label">【TEIテキストの出版社】</span>{{ bibliography.publisher }}</p>
      <p class="mb-1" v-if="bibliography.licence">
        <span class="label">【ライセンス】</span>
        <a v-if="bibliography.licence_url" target="_blank" rel="noopener noreferrer" :href="bibliography.licence_url">{{ bibliography.licence }}</a>
        <span v-else>{{ bibliography.licence }}</span>
      </p>
      <p v-for="note in bibliography.notes" class="mb-1"><span class="label">【注記】</span>{{ note }}</p>
      <!-- 以降xmlの記載箇所不明 -->
      <!-- <p>翻刻の底本：「〇〇」〇号p23-26（XMLに記載がない？）</p> -->
    </template>
  </Modal>
</template>
<style lang="scss" scoped>
.modal-button {
  a {
    text-decoration: none;
    cursor: pointer;
  }
}
.label {
  font-weight: bold;
}
</style>
