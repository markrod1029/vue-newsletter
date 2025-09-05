<template>
  <aside class="w-64 bg-white shadow-sm h-screen sticky top-0">
    <div class="p-4">
      <h2 class="text-lg font-semibold text-gray-900">Navigation</h2>
    </div>
    
    <nav class="mt-4">
      <div v-for="(group, groupIndex) in navigation" :key="groupIndex" class="mb-6">
        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          {{ group.group }}
        </h3>
        
        <div class="mt-2">
          <router-link
            v-for="item in group.items"
            :key="item.name"
            :to="item.to"
            class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900"
            :class="[isCurrentRoute(item.to) ? 'bg-gray-100 text-gray-900' : '']"
          >
            <CIcon :name="item.icon" class="mr-3 h-5 w-5" />
            {{ item.name }}
          </router-link>
        </div>
      </div>
    </nav>
  </aside>
</template>

<script>
import { CIcon } from '@coreui/icons-vue'
import { useAuthStore } from '../../stores/auth'
import { computed } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Sidebar',
  components: {
    CIcon
  },
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()

    const navigation = computed(() => {
      const baseItems = [
        {
          group: 'Main',
          items: [
            { name: 'Home', to: '/', icon: 'cil-home' },
            { name: 'News Feed', to: '/feed', icon: 'cil-newspaper' },
            { name: 'Events', to: '/events', icon: 'cil-calendar' },
            { name: 'Forums', to: '/forums', icon: 'cil-chat-bubble' }
          ]
        }
      ]

      if (authStore.user?.roles?.some(role => role.name === 'admin')) {
        baseItems.push({
          group: 'Admin',
          items: [
            { name: 'Dashboard', to: '/admin', icon: 'cil-speedometer' },
            { name: 'Pending Students', to: '/admin/students', icon: 'cil-user' },
            { name: 'Content Approval', to: '/admin/content', icon: 'cil-check' }
          ]
        })
      }

      if (authStore.user?.roles?.some(role => role.name === 'student')) {
        baseItems.push({
          group: 'Student',
          items: [
            { name: 'Dashboard', to: '/student', icon: 'cil-speedometer' },
            { name: 'Create Post', to: '/student/post/create', icon: 'cil-plus' }
          ]
        })
      }

      return baseItems
    })

    const isCurrentRoute = (route) => {
      return router.currentRoute.value.path === route
    }

    return {
      navigation,
      isCurrentRoute
    }
  }
}
</script>