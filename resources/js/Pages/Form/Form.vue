<script setup>
const props = defineProps({
  form: Object,
  parent_props: Object,
});
const form = props.form;
const parent_props = props.parent_props;
</script>
<template>
  <div class="row align-items-center gap-2 justify-content-center mb-5">
    <div class="col-lg-10 col-xl-9 col-xxl-8"><label class="form-label mb-0 fw-medium" for="tei">TEI/XMLファイル</label></div>
    <div class="col-lg-10 col-xl-9 col-xxl-8" v-if="form.tei === '' || form.tei === null">
      <input class="form-control" :class="{ 'is-invalid': !!parent_props.errors.tei }" type="file" accept=".xml" id="tei" @input="form.tei = $event.target.files[0]" />
      <p class="fs-sm text-muted mb-0"><small></small></p>
      <div class="invalid-feedback">{{ parent_props.errors.tei }}</div>
    </div>
    <div class="col-lg-10 col-xl-9 col-xxl-8" v-else>
      <div class="input-group" :class="{ 'is-invalid': !!parent_props.errors.tei }">
        <button type="button" :id="'remove_tei'" class="btn btn-dark btn-file-delete" @click="form.tei = null">取消</button>
        <span class="form-control">
          {{ form.tei.name }}
        </span>
      </div>
      <div class="invalid-feedback">{{ parent_props.errors.tei }}</div>
    </div>
  </div>
</template>
