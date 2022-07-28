<template>
  <div class="home">
    <header>Register Employee</header>
    <div class="body">
      <label for="email">Email <span class="required">*</span></label>
      <input v-model="email" type="text" name="email" required />
      <label for="password">Password <span class="required">*</span></label>
      <input v-model="password" type="password" name="password" required />
      <label for="confirm_password"
        >Confirm Password <span class="required">*</span></label
      >
      <input
        v-model="confirmPassword"
        type="password"
        name="confirm_password"
        required
      />
      <button @click="register">Create Account</button>
      <div class="row-mb-3" v-if="errors">
        <div
          class="col-md-12 error"
          v-for="(error, index) in errors"
          v-bind:key="index"
        >
          {{ error[0] }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// @ is an alias to /src
import { inject, ref } from "@vue/runtime-core";
import router from "@/router";

export default {
  name: "RegisterEmployee",
  components: {},
  setup() {
    const axios = inject("axios");

    const email = ref(null);
    const password = ref(null);
    const confirmPassword = ref(null);
    const errors = ref([]);

    function register() {
      axios
        .put(
          "http://localhost:8000/api/user/employee/register",
          {
            email: email.value,
            password: password.value,
            confirmPassword: confirmPassword.value,
          },
          {
            withCredentials: true,
          }
        )
        .then((response) => {
          if (response && response.data) {
            alert(`The user (${email.value}) has been created`);
            router.push('/home');
          }
        })
        .catch((error) => {
          console.warn(error);
          if (error.response.data && error.response.data.errors)
            errors.value = error.response.data.errors;
        });
    }

    return {
      register,
      email,
      password,
      confirmPassword,
      errors,
    };
  },
};
</script>


<style scoped>
.error {
  font-size: 0.7rem;
  color: red;
  margin-top: 0.5rem;
}
</style>