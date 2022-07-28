<template>
  <div class="home">
    <div class="modal" v-if="showAddPointsModal">
      <div class="modal-body">
        <div class="close-modal" @click="showAddPointsModal = false">X</div>
        <p class="modal-header">Add Points to User (ID: {{ modalUser.id }})</p>
        <input
          type="number"
          min="1"
          v-model="modalUserPoints"
          placeholder="Amount of points"
        />
        <p v-if="addPointError" class="modal-error">{{ addPointError }}</p>
        <button @click="addUserPoints">Add</button>
      </div>
    </div>

    <div class="modal" v-if="showRemovePointsModal">
      <div class="modal-body">
        <div class="close-modal" @click="showRemovePointsModal = false">X</div>
        <p class="modal-header">
          Remove Points to User (ID: {{ modalUser.id }})
        </p>
        <input
          type="number"
          min="1"
          :max="modalUser.points"
          v-model="modalUserPoints"
          placeholder="Amount of points"
        />
        <p v-if="removePointError" class="modal-error">
          {{ removePointError }}
        </p>
        <button @click="removeUserPoints">Remove</button>
      </div>
    </div>

    <div class="modal" v-if="showDeleteUserModal">
      <div class="modal-body">
        <div class="close-modal" @click="showDeleteUserModal = false">X</div>
        <p class="modal-header">Confirm Delete User (ID: {{ modalUser.id }})</p>
        <button class="delete-user-btn" @click="deleteUser">Delete</button>
      </div>
    </div>

    <header>Welcome</header>
    <div v-if="user.auth_level === 1 || !user.auth_level" class="body">
      <h3>Your Account's Balance</h3>
      <p>Is worth <strong>£{{ Math.round(usersPoints / 100, 2) ? Math.round(usersPoints / 100, 2) : 0 }}</strong></p>
      <h4>{{ user.points ?? 0 }}</h4>
      <p class="points">points</p>
      <div class="loyalty-card">
        <p>Your Loyalty Card</p>
        <p>
          Card ID: <span>{{ user.id }}</span>
        </p>
      </div>
      <button class="logout-btn" @click="logout">Logout</button>
    </div>
    <div v-else class="body">
      <a href="./employee/register">
        <button v-if="user.auth_level === 3">New Employee +</button>
      </a>
      <h3>Search for User</h3>
      <input v-model="searchQuery" type="text" />
      <button @click="search">Search</button>
      <hr />
      <div v-if="users" class="users">
        <div v-if="user.auth_level === 2 && user.auth_level === 3">
          <user
            v-for="foundUser in users"
            v-bind:key="foundUser.id"
            :user="foundUser"
            @addPointsModal="addPointsModal"
            @removePointsModal="removePointsModal"
          />
        </div>
        <div v-else>
          <user
            v-for="foundUser in users"
            v-bind:key="foundUser.id"
            :user="foundUser"
            @addPointsModal="addPointsModal"
            @removePointsModal="removePointsModal"
            @delete="deleteUserModal"
            :allowDelete="true"
          />
        </div>
      </div>
      <button class="logout-btn" @click="logout">Logout</button>
    </div>
  </div>
</template>

<script>
import { computed, inject, ref } from "@vue/runtime-core";
// @ is an alias to /src
import router from "@/router";
import User from "@/components/User.vue";

export default {
  name: "Home",
  components: {
    User,
  },
  setup() {
    const axios = inject("axios");
    const user = computed(() => JSON.parse(window.localStorage._user));
    const searchQuery = ref("");
    const users = ref([]);
    const modalUser = ref(null);
    const showAddPointsModal = ref(false);
    const showRemovePointsModal = ref(false);
    const showDeleteUserModal = ref(false);
    const modalUserPoints = ref(0);
    const addPointError = ref(null);
    const removePointError = ref(null);

    function logout() {
      axios
        .get("http://localhost:8000/api/logout", {
          withCredentials: true,
        })
        .then(() => {
          window.localStorage._token = null;
          window.localStorage._user = null;
          router.push("./login");
        })
        .catch((error) => console.warn(error));
    }

    function search() {
      if (searchQuery.value)
        axios
          .get(`http://localhost:8000/api/users/search/${searchQuery.value}`, {
            withCredentials: true,
          })
          .then((response) => {
            users.value = response.data;
          })
          .catch((error) => console.warn(error));
    }

    function addPointsModal(user) {
      showAddPointsModal.value = true;
      modalUser.value = user;
      modalUserPoints.value = 0;
    }

    function addUserPoints() {
      axios
        .post(
          `http://localhost:8000/api/user/${modalUser.value.id}/addPoints`,
          {
            points: modalUserPoints.value,
          },
          {
            withCredentials: true,
          }
        )
        .then((response) => {
          showAddPointsModal.value = false;
          addPointError.value = null;
          modalUser.value.points = response.data.user.points;
        })
        .catch((error) => {
          console.warn(error);
          if (error.response.data.msg)
            addPointError.value = error.response.data.msg;
        });
    }

    function removePointsModal(user) {
      showRemovePointsModal.value = true;
      modalUser.value = user;
      modalUserPoints.value = 0;
    }

    function deleteUserModal(user) {
      modalUser.value = user;
      showDeleteUserModal.value = true;
    }

    function removeUserPoints() {
      axios
        .post(
          `http://localhost:8000/api/user/${modalUser.value.id}/removePoints`,
          {
            points: modalUserPoints.value,
          },
          {
            withCredentials: true,
          }
        )
        .then((response) => {
          showRemovePointsModal.value = false;
          removePointError.value = null;
          modalUser.value.points = response.data.newPoints;
        })
        .catch((error) => {
          console.warn(error);
          if (error.response.data.msg)
            removePointError.value = error.response.data.msg;
        });
    }

    function deleteUser() {
      axios
        .delete(`http://localhost:8000/api/user/${modalUser.value.id}`, {
          withCredentials: true,
        })
        .then(() => (showDeleteUserModal.value = false))
        .catch((error) => console.warn(error));
    }

    const usersPoints = computed(() => {
      if (user.value.total_collected_points > 20000) {
        return user.value.points * 1.1;
      } else if (user.value.total_collected_points > 50000) {
        return user.value.points * 1.2;
      } else if (user.value.total_collected_points > 100000) {
        return user.value.points * 1.5;
      } else return user.value.points;
    })

    return {
      user,
      logout,
      searchQuery,
      search,
      users,
      addPointsModal,
      showAddPointsModal,
      modalUser,
      modalUserPoints,
      addUserPoints,
      removePointsModal,
      removeUserPoints,
      showRemovePointsModal,
      showDeleteUserModal,
      deleteUserModal,
      deleteUser,
      addPointError,
      removePointError,
      usersPoints
    };
  },
};
</script>


<style scoped>
h3,
h4 {
  font-weight: normal;
  margin: 0;
}

h4 {
  margin-top: 0.5rem;
  font-size: 1.5rem;
}

p.points {
  margin: 1rem 0 3rem 0;
}

.body {
  align-items: center;
}

.loyalty-card {
  background-color: #00519b;
  padding: 1.5rem;
  border-radius: 1.5rem;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  margin-bottom: 1rem;
}

.logout-btn,
.delete-user-btn {
  background-color: red;
}

.users {
  max-height: 50vh;
  overflow-y: auto;
  margin-bottom: 1rem;
}
</style>