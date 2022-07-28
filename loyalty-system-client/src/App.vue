<template>
  <div id="nav">
    <a href="/home">
      <button class="home-btn">Home</button>
    </a>
    <router-view />
  </div>
</template>

<style>
html,
body {
  margin: 0;
  padding: 0;
  font-family: Arial, Helvetica, sans-serif;
  background-color: #eeeeee;
}

a,
.hyperlink {
  text-decoration: none;
  color: #00519b;
}

span.required {
  color: red;
}
header {
  width: calc(100% - 2rem);
  background-color: #00519b;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  min-height: 6rem;
  font-size: 1.75rem;
}

.body {
  display: flex;
  justify-content: center;
  flex-direction: column;
  background-color: white;
  width: 80%;
  padding: 1rem;
  margin: 0 auto;
  transform: translateY(-1rem);
}

button {
  background-color: #00519b;
  border-radius: 0.75rem;
  color: white;
  border: unset;
  padding: 0.5rem;
  cursor: pointer;
}

input {
  margin-bottom: 1rem;
  margin-top: 0.35rem;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 10;
  background-color: rgba(125, 125, 125, 0.5);
}

.modal-body {
  background-color: white;
  width: max-content;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  padding: 1rem;
  max-width: 80vw;
}

.close-modal {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  cursor: pointer;
}

.modal-error {
  color: Red;
  margin: 0;
  margin-bottom: 1rem;
  font-size: 0.75rem;
}

.home-btn {
  position: fixed;
  top: 1rem;
  left: 1rem;
}
</style>
