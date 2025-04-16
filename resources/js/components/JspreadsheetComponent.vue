<template>
  <div ref="spreadsheetContainer"></div>
</template>

<script>
import { ref, onMounted } from 'vue'
import jspreadsheet from 'jspreadsheet-ce'
import 'jspreadsheet-ce/dist/jspreadsheet.css'

export default {
  name: 'JspreadsheetComponent',
  setup() {
    const spreadsheetContainer = ref(null)

    onMounted(() => {
      const config = {
        // Example data: first row could be column headers or regular cells
        data: [
          ['', 'T-Shirt', 'Pants'],
          ['Size', 'XL', 'M']
        ],
        // Define the columns: type, title and width settings
        columns: [
          { type: 'text', title: 'Product', width: 120 },
          {
            type: 'dropdown',
            title: 'Option',
            width: 120,
            // Provide an array of options for the dropdown cell editor
            source: ['Option1', 'Option2', 'Option3']
          },
          { type: 'text', title: 'Details', width: 150 }
        ]
      }

      // Output the configuration to the console for verification
      console.log('Jspreadsheet config:', config)

      // Initialize the spreadsheet inside the referenced container
      jspreadsheet(spreadsheetContainer.value, config)
    })

    return { spreadsheetContainer }
  }
}
</script>

<style scoped>
/* Optional styling, for example define a minimum height for the container */
div {
  min-height: 400px;
}
</style>
