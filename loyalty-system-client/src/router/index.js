import { createRouter, createWebHistory } from "vue-router";
import Login from "../views/Login.vue";
import Home from "../views/Home.vue";
import Register from "../views/Register.vue";
import RegisterEmployee from "../views/RegisterEmployee.vue";
import PageNotFound404 from "../views/PageNotFound404.vue";

const routes = [
  {
    path: "/",
    name: "Login",
    component: Login,
    meta: {
      requiresUnauth: true
    }
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
    meta: {
      requiresUnauth: true
    }
  },
  {
    path: "/home",
    name: "Home",
    component: Home,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: "/register",
    name: "Register",
    component: Register,
    meta: {
      requiresUnauth: true
    }
  },
  {
    path: "/employee/register",
    name: "RegisterEmployee",
    component: RegisterEmployee,
    meta: {
      requiresAuth: true,
      requiresAuth3: true
    }
  },
  {
    path: "/:pathMatch(.*)*",
    name: "PageNotFound404",
    component: PageNotFound404,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const requiresAuth3 = to.matched.some(record => record.meta.requiresAuth3);
  const requiresUnauth = to.matched.some(record => record.meta.requiresUnauth);

  const _token = window.localStorage._token;
  const authUser = window.localStorage._user;

  if (requiresAuth) {
    if (requiresAuth3) {
      (_token && _token != 'null' && authUser && authUser != 'null' && JSON.parse(authUser).auth_level === 3) ? next() : next('/login');
    } else {
      _token && _token != 'null' && authUser && authUser != 'null' ? next() : next('/login');
    }
  }

  if (requiresUnauth) {
    !_token || _token == 'null' ? next() : next('/home');
  }

  next();
})


export default router;
