<template>
  <div class="home">
    <header>Welcome</header>
    <div class="body">
      <label for="email">Email <span class="required">*</span></label>
      <input v-model="email" type="text" name="email" required />
      <label for="password">Password <span class="required">*</span></label>
      <input v-model="password" type="password" name="password" required />
      <p class="error" v-if="invalid">The credentials entered are invalid</p>
      <button @click="login">Login</button>
      <p id="register-link" class="hyperlink" @click="goToRegistration">
        Register
      </p>
    </div>
  </div>
</template>

<script>
// @ is an alias to /src
import { inject, ref } from "@vue/runtime-core";
import router from "@/router";

export default {
  name: "Login",
  components: {},
  setup() {
    const axios = inject("axios");

    const email = ref(null);
    const password = ref(null);
    const invalid = ref(null);

    function login() {
      axios
        .post(
          "http://localhost:8000/api/login",
          {
            email: email.value,
            password: password.value,
          },
          {
            withCredentials: true,
          }
        )
        .then((response) => {
          if (response && response.data && response.data.token)
            window.localStorage._token = response.data.token;

          if (response && response.data && response.data.user)
            window.localStorage._user = JSON.stringify(response.data.user);

          router.push("./home");
        })
        .catch((error) => {
          console.warn(error);
          invalid.value = true;
        });
    }

    function goToRegistration() {
      router.push("./register");
    }

    return {
      login,
      email,
      password,
      invalid,
      goToRegistration,
    };
  },
};
</script>


<style scoped>
#register-link {
  margin-bottom: 0;
  width: max-content;
  margin: 1rem auto 0 auto;
  font-size: 0.75rem;
}

.error {
  font-size: 0.75rem;
  color: red;
  margin: 0;
  margin-bottom: 1rem;
}
</style>