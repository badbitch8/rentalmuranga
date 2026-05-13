<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container-wide py-6 md:py-8">
      <div class="card p-0 overflow-hidden" style="height: calc(100vh - 200px);">
        <div class="grid grid-cols-1 md:grid-cols-3 h-full">
          <!-- Conversations List -->
          <div class="md:col-span-1 border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 mb-3">Messages</h2>
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search conversations..."
                  class="input-field pl-9"
                />
                <svg class="icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
            </div>

            <!-- Conversations -->
            <div class="flex-1 overflow-y-auto">
              <LoadingSpinner v-if="loadingConversations" size="sm" class="py-8" />
              
              <div v-else-if="filteredConversations.length > 0">
                <div
                  v-for="conversation in filteredConversations"
                  :key="conversation.id"
                  @click="selectConversation(conversation)"
                  :class="[
                    'p-4 border-b border-gray-200 cursor-pointer transition-colors',
                    selectedConversation?.id === conversation.id ? 'bg-blue-50' : 'hover:bg-gray-50'
                  ]"
                >
                  <div class="flex items-start gap-3">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                      <span class="text-sm font-semibold text-gray-600">
                        {{ conversation.user?.name?.charAt(0) }}
                      </span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-start justify-between mb-1">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                          {{ conversation.user?.name }}
                        </h3>
                        <span class="text-xs text-gray-500 flex-shrink-0 ml-2">
                          {{ formatTime(conversation.last_message?.created_at) }}
                        </span>
                      </div>
                      <p class="text-xs text-gray-600 mb-1 truncate">
                        {{ conversation.property?.title }}
                      </p>
                      <p class="text-sm text-gray-600 truncate">
                        {{ conversation.last_message?.content }}
                      </p>
                      <span v-if="conversation.unread_count > 0" class="inline-block mt-1 px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full">
                        {{ conversation.unread_count }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-12 px-4">
                <svg class="icon-xl mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <p class="text-sm text-gray-600">No conversations yet</p>
              </div>
            </div>
          </div>

          <!-- Chat Area -->
          <div class="md:col-span-2 flex flex-col">
            <!-- No Conversation Selected -->
            <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center text-center p-8">
              <div>
                <svg class="icon-xl mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Select a conversation</h3>
                <p class="text-sm text-gray-600">Choose a conversation from the list to start messaging</p>
              </div>
            </div>

            <!-- Active Conversation -->
            <template v-else>
              <!-- Chat Header -->
              <div class="p-4 border-b border-gray-200 bg-white">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                      <span class="text-sm font-semibold text-gray-600">
                        {{ selectedConversation.user?.name?.charAt(0) }}
                      </span>
                    </div>
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">
                        {{ selectedConversation.user?.name }}
                      </h3>
                      <p class="text-xs text-gray-600">{{ selectedConversation.property?.title }}</p>
                    </div>
                  </div>
                  <button @click="viewProperty(selectedConversation.property?.id)" class="btn-outline text-sm">
                    View Property
                  </button>
                </div>
              </div>

              <!-- Messages -->
              <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                <LoadingSpinner v-if="loadingMessages" size="sm" />
                
                <div v-else-if="messages.length > 0">
                  <div
                    v-for="message in messages"
                    :key="message.id"
                    :class="[
                      'flex',
                      message.is_mine ? 'justify-end' : 'justify-start'
                    ]"
                  >
                    <div :class="[
                      'max-w-xs md:max-w-md lg:max-w-lg rounded-lg p-3',
                      message.is_mine ? 'bg-blue-600 text-white' : 'bg-white text-gray-900'
                    ]">
                      <p class="text-sm break-words">{{ message.content }}</p>
                      <div v-if="message.attachment" class="mt-2">
                        <a :href="message.attachment.url" target="_blank" class="text-xs underline flex items-center gap-1">
                          <svg class="icon-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                          </svg>
                          {{ message.attachment.name }}
                        </a>
                      </div>
                      <p :class="['text-xs mt-1', message.is_mine ? 'text-blue-100' : 'text-gray-500']">
                        {{ formatMessageTime(message.created_at) }}
                      </p>
                    </div>
                  </div>
                </div>

                <div v-else class="text-center py-8 text-gray-500">
                  <p class="text-sm">No messages yet. Start the conversation!</p>
                </div>
              </div>

              <!-- Message Input -->
              <div class="p-4 border-t border-gray-200 bg-white">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                  <button
                    type="button"
                    @click="attachFile"
                    class="btn-outline p-2 flex-shrink-0"
                    title="Attach file"
                  >
                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                  </button>
                  <input
                    v-model="newMessage"
                    type="text"
                    placeholder="Type your message..."
                    class="input-field flex-1"
                    :disabled="sending"
                  />
                  <button
                    type="submit"
                    :disabled="!newMessage.trim() || sending"
                    class="btn-primary px-6 flex-shrink-0"
                  >
                    <span v-if="!sending">Send</span>
                    <span v-else>Sending...</span>
                  </button>
                </form>
                <input
                  ref="fileInput"
                  type="file"
                  class="hidden"
                  @change="handleFileSelect"
                />
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()
const route = useRoute()

const searchQuery = ref('')
const conversations = ref([])
const selectedConversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const loadingConversations = ref(false)
const loadingMessages = ref(false)
const sending = ref(false)
const messagesContainer = ref(null)
const fileInput = ref(null)

const filteredConversations = computed(() => {
  if (!searchQuery.value) return conversations.value
  const query = searchQuery.value.toLowerCase()
  return conversations.value.filter(conv =>
    conv.user?.name?.toLowerCase().includes(query) ||
    conv.property?.title?.toLowerCase().includes(query)
  )
})

const formatTime = (date) => {
  const now = new Date()
  const messageDate = new Date(date)
  const diffInHours = (now - messageDate) / (1000 * 60 * 60)
  
  if (diffInHours < 24) {
    return messageDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  } else if (diffInHours < 48) {
    return 'Yesterday'
  } else {
    return messageDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  }
}

const formatMessageTime = (date) => {
  return new Date(date).toLocaleTimeString('en-US', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const selectConversation = async (conversation) => {
  selectedConversation.value = conversation
  await loadMessages(conversation.id)
  scrollToBottom()
}

const loadConversations = async () => {
  loadingConversations.value = true
  try {
    // TODO: Implement API call
    // const response = await api.get('/messages/conversations')
    // conversations.value = response.data.data
    
    // Mock data
    conversations.value = [
      {
        id: 1,
        user: { id: 2, name: 'John Kamau' },
        property: { id: 1, title: 'Modern 1BR Apartment' },
        last_message: {
          content: 'Is the property still available?',
          created_at: new Date().toISOString()
        },
        unread_count: 2
      },
      {
        id: 2,
        user: { id: 3, name: 'Mary Wanjiku' },
        property: { id: 2, title: 'Cozy Bedsitter' },
        last_message: {
          content: 'Thank you for your interest!',
          created_at: new Date(Date.now() - 86400000).toISOString()
        },
        unread_count: 0
      }
    ]
  } catch (error) {
    console.error('Error loading conversations:', error)
  } finally {
    loadingConversations.value = false
  }
}

const loadMessages = async (conversationId) => {
  loadingMessages.value = true
  try {
    // TODO: Implement API call
    // const response = await api.get(`/messages/conversations/${conversationId}`)
    // messages.value = response.data.data
    
    // Mock data
    messages.value = [
      {
        id: 1,
        content: 'Hello, I\'m interested in this property.',
        is_mine: true,
        created_at: new Date(Date.now() - 3600000).toISOString()
      },
      {
        id: 2,
        content: 'Great! The property is still available. Would you like to schedule a viewing?',
        is_mine: false,
        created_at: new Date(Date.now() - 3000000).toISOString()
      },
      {
        id: 3,
        content: 'Yes, that would be perfect. When are you available?',
        is_mine: true,
        created_at: new Date(Date.now() - 1800000).toISOString()
      }
    ]
  } catch (error) {
    console.error('Error loading messages:', error)
  } finally {
    loadingMessages.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return
  
  sending.value = true
  try {
    // TODO: Implement API call
    // await api.post('/messages', {
    //   conversation_id: selectedConversation.value.id,
    //   content: newMessage.value
    // })
    
    // Add message to list
    messages.value.push({
      id: Date.now(),
      content: newMessage.value,
      is_mine: true,
      created_at: new Date().toISOString()
    })
    
    newMessage.value = ''
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error sending message:', error)
  } finally {
    sending.value = false
  }
}

const attachFile = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    // TODO: Implement file upload
    console.log('File selected:', file.name)
  }
}

const viewProperty = (propertyId) => {
  router.push(`/properties/${propertyId}`)
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

onMounted(async () => {
  await loadConversations()
  
  // Auto-select conversation if user ID is in query params
  const userId = route.query.user
  if (userId && conversations.value.length > 0) {
    const conversation = conversations.value.find(c => c.user.id === parseInt(userId))
    if (conversation) {
      selectConversation(conversation)
    }
  }
})
</script>