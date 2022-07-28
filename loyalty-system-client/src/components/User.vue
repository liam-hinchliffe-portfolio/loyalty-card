<template>
  <div class="user">
    <div class="col">
      <p>ID: {{ user.id }}</p>
      <p>Email: {{ user.email }}</p>
    </div>
    <div class="col">
      <p>Points: {{ user.points }}</p>
      <div class="buttons">
        <button @click="addPointsModal">+</button>
        <button @click="removePointsModal">-</button>
      </div>
    </div>
    <div class="col" v-if="authUser.auth_level == 3">
      <button v-if="!user.deleted_at || isRestored" class="delete-btn" @click="deleteModal">
        Delete
      </button>
      <button v-else class="restore-btn" @click="restore">Restore</button>
    </div>
  </div>
</template>
<script>
import { defineComponent, inject, ref, computed } from "vue";
export default defineComponent({
  name: "User",
  props: {
    user: { type: Object, required: true },
    allowDelete: { type: Boolean, required: false, default: false },
  },
  setup(props, context) {
    const axios = inject("axios");
    const authUser = computed(() => JSON.parse(window.localStorage._user));

    function restore() {
      axios
        .post(
          `http://localhost:8000/api/user/${props.user.id}/restore`,
          {},
          {
            withCredentials: true,
          }
        )
        .then(() => {
          console.log("restored")
          isRestored.value = true;
        })
        .catch((error) => console.warn(error));
    }

    const isRestored = ref(false);

    function addPointsModal() {
      context.emit("addPointsModal", props.user);
    }
    function removePointsModal() {
      context.emit("removePointsModal", props.user);
    }

    function deleteModal() {
      context.emit("delete", props.user);
    }
    return {
      addPointsModal,
      removePointsModal,
      deleteModal,
      restore,
      isRestored,
      authUser
    };
  },
});
</script>
<style>
.user {
  background-color: #00519b;
  padding: 1rem;
  color: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-direction: row;
  margin-bottom: 1rem;
}

.col {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  flex-direction: column;
}

.col > p {
  width: 100%;
}

.buttons {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-direction: row;
}

.buttons > button {
  background-color: white;
  border-radius: 50%;
  width: 1rem;
  height: 1rem;
  color: #00519b;
  display: flex;
  align-items: center;
  justify-content: center;
}

.buttons > button:first-child {
  margin-right: 0.5rem;
}

.delete-btn {
  background-color: red;
}

.restore-btn {
  background-color: white;
  color: #00519b;
}
</style>
