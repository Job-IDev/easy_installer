<template>
  <div class="easy-installer">
    <h2>Install Custom Nextcloud App</h2>
    
    <div class="form-group">
      <label for="appId">App ID (e.g., my_app_name):</label>
      <input type="text" id="appId" v-model="appId" placeholder="App ID from the info.xml" />
    </div>

    <div class="form-group">
      <label for="zipFile">Select App ZIP File:</label>
      <input type="file" id="zipFile" accept=".zip" @change="onFileChange" />
    </div>

    <button class="primary" @click="uploadZip" :disabled="loading || !file || !appId">
      {{ loading ? 'Installing...' : 'Install App' }}
    </button>
    
    <p v-if="message" :class="{'success': isSuccess, 'error': !isSuccess}" class="status-msg">
      {{ message }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const appId = ref('')
const file = ref<File | null>(null)
const loading = ref(false)
const message = ref('')
const isSuccess = ref(false)

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    file.value = target.files[0]
  }
}

const uploadZip = async () => {
  if (!file.value || !appId.value) return
  
  loading.value = true
  message.value = ''
  
  const formData = new FormData()
  formData.append('app_zip', file.value)
  formData.append('appId', appId.value)
  
  try {
    // Generate URL dynamically for the Nextcloud route we defined earlier
    const url = generateUrl('/apps/easy_installer/api/upload')
    const response = await axios.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (response.data.status === 'success') {
      isSuccess.value = true
      message.value = 'App installed and activated successfully!'
    }
  } catch (error: any) {
    isSuccess.value = false
    message.value = error.response?.data?.error || 'Installation failed.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.easy-installer {
  padding: 30px;
  max-width: 500px;
  background: var(--color-main-background);
  border-radius: var(--border-radius-large);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.form-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
}
.form-group label {
  margin-bottom: 8px;
  font-weight: bold;
}
.form-group input[type="text"] {
  padding: 8px;
  border-radius: var(--border-radius);
  border: 1px solid var(--color-border);
}
button {
  padding: 10px 20px;
  border-radius: var(--border-radius-pill);
  cursor: pointer;
}
.status-msg {
  margin-top: 15px;
  font-weight: bold;
}
.error { color: var(--color-error, red); }
.success { color: var(--color-success, green); }
</style>