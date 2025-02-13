<template>
  <!-- The ul's inline style sets no left padding or margin for level 0 -->
  <ul
    class="directory-tree"
    :style="{
      paddingLeft: level === 0 ? '0px' : 20 + 'px',
      margin: level === 0 ? '0px' : 'initial'
    }"
  >
    <li v-for="node in localNodes" :key="node.path">
      <div class="directory-label">
        <!-- Disclosure arrow (if the node has children) -->
        <span
          class="toggle-icon"
          v-if="node.children && node.children.length"
          @click="toggle(node)"
        >
          <i
            :class="[
              'fa-solid',
              node.isOpen ? 'fa-caret-down' : 'fa-caret-right',
              'text-warning'
            ]"
          ></i>
        </span>
        <!-- Placeholder for alignment if there are no children -->
        <span
          class="toggle-icon"
          v-else
          style="width: 16px; display: inline-block;"
        ></span>

        <!-- Folder icon using your preferred style -->
        <i class="fa-solid fa-folder text-warning"></i>

        <!-- Directory name as a navigation link -->
        <a :href="`/file-manager-personalizat/${node.path}`" class="directory-link">
          {{ node.name }}
        </a>
      </div>

      <!-- Recursive rendering: display children if the node is open -->
      <component
        v-if="node.children && node.children.length && node.isOpen"
        :is="$options.name"
        :nodes="node.children"
        :level="level + 1"
      ></component>
    </li>
  </ul>
</template>

<script>
export default {
  name: "DirectoryTree",
  props: {
    nodes: {
      type: Array,
      required: true,
    },
    // Prop to keep track of the nesting level (0 for top-level)
    level: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      // Create a deep clone of the prop for local reactive state.
      localNodes: JSON.parse(JSON.stringify(this.nodes)),
    };
  },
  methods: {
    toggle(node) {
      if (node.children && node.children.length) {
        node.isOpen = !node.isOpen;
      }

        // Log just the toggled node
        console.log("Toggled node:", node);

        // Or log *all* localNodes (the entire tree state)
        // so you can see the new state of all nodes
        console.log("All node states:", JSON.stringify(this.localNodes, null, 2));
    },
  },
  mounted() {
    console.log("DirectoryTree mounted with localNodes:", this.localNodes);
  },
};
</script>

<style scoped>
.directory-tree {
  list-style: none;
  padding: 0; /* Remove default padding */
  margin: 0;  /* Remove default margin */
}

li {
  margin-bottom: 0px; /* Reduced spacing between list items */
}

.directory-label {
  display: flex;
  align-items: center;
  padding: 0px 0;  /* Reduced vertical padding */
}

.toggle-icon {
  cursor: pointer;
  display: inline-block;
  width: 16px;
  text-align: center;
}

.directory-link {
  margin-left: 5px;
  text-decoration: none;
  color: inherit;
}
</style>
