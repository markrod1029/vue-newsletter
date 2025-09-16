import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

// Import components
import Login from "../components/auth/Login.vue";
import Register from "../components/auth/Register.vue";
import Home from "../views/Home.vue";
import Feed from "../views/Feed.vue";
import FeedView from "../views/PostDetail.vue";
import Search from "../views/search.vue";
import Forums from "../views/Forums.vue";
import ForumThread from "../views/ForumThread.vue";
import Events from "../views/Events.vue";
import EventView from "../views/EventDetails.vue";
import AdminDashboard from "../components/admin/Dashboard.vue";
import CreatePost from "../components/admin/CreatePost.vue";
import CreateEvent from "../components/admin/CreateEvent.vue";
import PendingStudents from "../components/admin/PendingStudents.vue";
import PendingContent from "../components/admin/PendingContent.vue";
import StudentDashboard from "../components/student/Dashboard.vue";
import PostCreate from "../components/student/PostCreate.vue";
import Profile from "../views/Profile.vue";

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
    meta: { requiresGuest: true },
  },
  {
    path: "/register",
    name: "Register",
    component: Register,
    meta: { requiresGuest: true },
  },
  {
    path: "/feed",
    name: "Feed",
    component: Feed,
    meta: { requiresAuth: true },
  },

  {
    path: "/feed/view/:id",
    name: "FeedView",
    component: FeedView,
    meta: { requiresAuth: true },
  },

  {
    path: "/news-feed",
    name: "news-feed",
    component: Search,
    meta: { requiresAuth: true },
  },
  {
    path: "/forums",
    name: "Forums",
    component: Forums,
    meta: { requiresAuth: true },
  },
  {
    path: "/forums/:id",
    name: "ForumThread",
    component: ForumThread,
    meta: { requiresAuth: true },
  },
  {
    path: "/events",
    name: "Events",
    component: Events,
    meta: { requiresAuth: true },
  },

  {
    path: "/profile",
    name: "Profile",
    component: Profile,
    meta: { requiresAuth: true },
  },

  {
    path: "/event/view/:id",
    name: "Event Details",
    component: EventView,
    meta: { requiresAuth: true },
  },

  // {
  //   path: "/events/:id",
  //   name: "EventThread",
  //   component: Events,
  //   meta: { requiresAuth: true },
  // },
  // Admin routes
  {
    path: "/admin",
    name: "AdminDashboard",
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/post/create",
    name: "AdminCreatePost",
    component: CreatePost,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
    {
    path: "/admin/post/edit/:id",
    name: "AdminPostEdit",
    component: CreatePost,
    meta: { requiresAuth: true, requiresAdmin: true },
  },

  {
    path: "/admin/create-event",
    name: "CreateEvent",
    component: CreateEvent,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/event/:id",
    name: "EventEdit",
    component: CreateEvent,
    meta: { requiresAuth: true, requiresAdmin: true },
  },

  {
    path: "/admin/students",
    name: "PendingStudents",
    component: PendingStudents,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: "/admin/content",
    name: "PendingContent",
    component: PendingContent,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  // Student routes
  {
    path: "/student",
    name: "StudentDashboard",
    component: StudentDashboard,
    meta: { requiresAuth: true, requiresStudent: true },
  },
  {
    path: "/student/post/create",
    name: "PostCreate",
    component: PostCreate,
    meta: { requiresAuth: true, requiresStudent: true },
  },

  {
    path: "/student/post/edit/:id",
    name: "PostEdit",
    component: PostCreate,
    meta: { requiresAuth: true, requiresStudent: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // If auth is not initialized yet, try to initialize it
  if (
    !authStore.isAuthenticated &&
    localStorage.getItem("auth_authenticated") === "true"
  ) {
    await authStore.fetchUser();
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next("/login");
    return;
  }

  // Check if route requires guest (non-authenticated)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next("/");
    return;
  }

  next();
});

export default router;
