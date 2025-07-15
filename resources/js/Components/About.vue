<script setup>
const props = defineProps({
  summary: String, // TEI内に記載している要約
});
</script>
<template>
  <div class="about-header py-2 ps-3 fw-bold fs-5">【資料概要】</div>
  <div class="about-text d-flex justify-content-center pt-3">
    <p class="px-4">{{ summary }}</p>
  </div>
</template>
<style lang="scss" scoped>
.about-header {
  height: 20%;
}
.about-text {
  height: 80%;
}
</style>
