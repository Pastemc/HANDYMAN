<template>
    <div class="wrapper">
        <TopBar />
        <Sidebar :unreadMessages="unreadMessages" :unreadNotifications="unreadNotifications" />
        
        <div class="content-wrapper">
            <router-view />
        </div>
        
        <Footer />
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import TopBar from '../components/TopBar.vue';
import Sidebar from '../components/Sidebar.vue';
import Footer from '../components/Footer.vue';

export default {
    name: 'AuthLayout',
    components: {
        TopBar,
        Sidebar,
        Footer,
    },
    setup() {
        const unreadNotifications = ref(0);
        const unreadMessages = ref(0);
        let refreshInterval = null;

        const fetchUnreadCounts = async () => {
            try {
                const notifResponse = await axios.get('/notifications/unread/count');
                unreadNotifications.value = notifResponse.data.count || 0;

                const convResponse = await axios.get('/conversations/unread/count');
                unreadMessages.value = convResponse.data.count || 0;
            } catch (error) {
                console.error('Error fetching counts:', error);
            }
        };

        onMounted(() => {
            fetchUnreadCounts();
            refreshInterval = setInterval(fetchUnreadCounts, 30000);
        });

        onUnmounted(() => {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });

        return {
            unreadNotifications,
            unreadMessages,
        };
    },
};
</script>