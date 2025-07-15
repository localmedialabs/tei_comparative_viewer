<script setup>
const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
  modalName: {
    type: String,
  },
  isExistHeader: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["close"]);

const closeModal = () => {
  emit("close");
};
</script>

<template>
  <transition name="fade">
    <div v-if="isVisible" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="px-4 my-auto d-flex border-bottom">
          <h3>
            {{ modalName }}
          </h3>
          <button @click="closeModal" type="button" class="btn-close ms-auto me-0"></button>
        </div>
        <div v-if="isExistHeader" class="modal-header">
          <slot name="header"></slot>
        </div>
        <div class="modal-body mt-2">
          <slot name="body"></slot>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s, transform 0.3s;
}
.fade-enter-from {
  opacity: 0;
}
.fade-leave-to {
  opacity: 0;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
.btn-close {
  padding: 16px;
}
.modal-content {
  width: 80%;
  height: 80%;
  background: white;
  padding: 20px 0;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.modal-header {
  display: block;
  padding: 16px;
  background: #f0f0f0;
}
.modal-body {
  padding: 16px;
  overflow-y: auto;
  flex-grow: 1;
}
</style>
