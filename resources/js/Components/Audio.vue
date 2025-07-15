<script setup>
const props = defineProps({
  audio_url: String, // 音声ファイルのURL
});
</script>
<template>
  <div class="audio-header pt-2 ps-3 fw-bold fs-5">【音声を再生】</div>
  <div class="audio-text d-flex justify-content-center py-2">
    <audio controls controlslist="nodownload" v-if="audio_url">
      <source :src="audio_url" type="audio/mpeg" />
      ブラウザが音声をサポートしていません。
    </audio>
  </div>
</template>
<style lang="scss" scoped>
audio {
  max-height: 40px;
}
</style>
