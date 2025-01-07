<template>
  <div class="tiptap-container rounded-3">
    <!-- Toolbar Menu -->
     <div class="editor-toolbar border rounded p-2 mb-0" style="background-color: lightgoldenrodyellow;">

        <div class="btn-toolbar mb-0" role="toolbar">
            <!-- Undo/Redo -->
            <div class="btn-group me-3" role="group">
                <button type="button" @click="undo" title="Undo"><i class="fa-solid fa-rotate-left"></i></button>
                <button type="button" @click="redo" title="Redo"><i class="fa-solid fa-rotate-right"></i></button>
            </div>

            <!-- Text Size Dropdown -->
            <div class="btn-group me-3">
                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    Mărime text: {{ currentTextSize }}
                </button>
                <ul class="dropdown-menu">
                    <button type="button" class="btn" @click="setTextSize('12px')">12px</button>
                    <button type="button" class="btn" @click="setTextSize('14px')">14px</button>
                    <button type="button" class="btn" @click="setTextSize('16px')">16px</button>
                    <button type="button" class="btn" @click="setTextSize('18px')">18px</button>
                    <button type="button" class="btn" @click="setTextSize('20px')">20px</button>
                    <button type="button" class="btn" @click="setTextSize('26px')">26px</button>
                </ul>
            </div>

            <!-- Formatting -->
            <div class="btn-group me-3" role="group">
                <button type="button" class="btn" @click="toggleBold" :class="{ active: isBoldActive }" title="Bold"><i class="fa-solid fa-bold"></i></button>
                <button type="button" class="btn" @click="toggleItalic" :class="{ active: isItalicActive }" title="Italic"><i class="fa-solid fa-italic"></i></button>
                <button type="button" class="btn" @click="toggleUnderline" :class="{ active: isUnderlineActive }" title="Underline"><i class="fa-solid fa-underline"></i></button>
                <button type="button" class="btn" @click="toggleStrike" :class="{ active: isStrikeActive }" title="Strikethrough"><i class="fa-solid fa-strikethrough"></i></button>
            </div>

            <!-- Text Alignment -->
            <div class="btn-group me-3">
                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-align-left"></i>
                </button>
                <ul class="dropdown-menu">
                    <button type="button" class="btn" @click="setTextAlign('left')" title="Align left"><i class="fa-solid fa-align-left me-1"></i></button>
                    <button type="button" class="btn" @click="setTextAlign('center')" title="Align center"><i class="fa-solid fa-align-center me-1"></i></button>
                    <button type="button" class="btn" @click="setTextAlign('right')" title="Align right"><i class="fa-solid fa-align-right me-1"></i></button>
                    <button type="button" class="btn" @click="setTextAlign('justify')" title="Text justify"><i class="fa-solid fa-align-justify me-1"></i></button>
                </ul>
            </div>

            <!-- Text Color -->
            <div class="btn-group me-2 d-flex align-items-center">
                <label class="form-label mb-0 me-1">Culoare text:</label>
                <input
                    type="color"
                    :value="currentTextColor"
                    @input="setTextColor($event.target.value)"
                    class="form-control-color"
                />
                <!-- <span class="current-color-display" :style="{ backgroundColor: currentTextColor }"></span> -->
            </div>

            <!-- Lists -->
            <div class="btn-group me-3" role="group">
                <button type="button" @click="toggleBulletList" title="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
                <button type="button" @click="toggleOrderedList" title="Ordered list"><i class="fa-solid fa-list-ol"></i></button>
            </div>

            <!-- Links -->
            <div class="btn-group me-3" role="group">
                <button type="button" @click="setLink" title="Set link"><i class="fa-solid fa-link"></i></button>
                <button type="button" @click="unsetLink" title="Unset link"><i class="fa-solid fa-link-slash"></i></button>
            </div>

            <!-- Table Controls -->
            <!-- <div class="btn-group me-3">
                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-table"></i>
                </button>
                <ul class="dropdown-menu">
                    <button type="button" class="btn border-0" @click="addTable">Adaugă tabel</button>
                    <button type="button" class="btn border-0" @click="addColumnBefore">← Adaugă Coloană</button>
                    <button type="button" class="btn border-0" @click="addColumnAfter">→ Adaugă Coloană</button>
                    <button type="button" class="btn border-0" @click="addRowBefore">↑ Adaugă rând</button>
                    <button type="button" class="btn border-0" @click="addRowAfter">↓ Adaugă rând</button>
                    <button type="button" class="btn border-0" @click="mergeCells">🔗 Merge Cells</button>
                    <button type="button" class="btn border-0" @click="splitCell">✂️ Split Cell</button>
                </ul>
            </div> -->
            <div class="btn-group me-3" role="group">
                <button type="button" class="btn border-0" @click="addTable" title="Adaugă tabel"><i class="fa-solid fa-table"></i></button>
            </div>

            <div class="btn-group me-2">
                <button type="button" class="btn" @click="toggleFullscreen" title="Fullscreen">
                    {{ isFullscreen ? 'Ieșire Fullscreen' : 'Fullscreen' }}
                    <!-- <i v-if="isFullscreen" class="fa-solid fa-minimize"></i> -->
                    <!-- <i v-else class="fa-solid fa-maximize"></i> -->
                </button>
            </div>
        </div>


            <!-- <button type="button" @click="addTable" title="Adaugă tabel"><i class="fa-solid fa-table"></i></button>
            <button type="button" @click="addColumnBefore">← Add Column</button>
            <button type="button" @click="addColumnAfter">→ Add Column</button>
            <button type="button" @click="deleteColumn">❌ Delete Column</button>
            <button type="button" @click="addRowBefore">↑ Add Row</button>
            <button type="button" @click="addRowAfter">↓ Add Row</button>
            <button type="button" @click="deleteRow">❌ Delete Row</button>
            <button type="button" @click="deleteTable">🗑️ Delete Table</button>
        </div> -->

        <!-- Force new line -->
        <div class="w-100"></div>

        <!-- Table Menu -->
        <div v-if="editor?.isActive('table')" class="btn-toolbar mb-0" role="toolbar">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn" @click="addRowBefore">↑ Rând înainte</button>
                <button type="button" class="btn" @click="addRowAfter">↓ Rând după</button>
                <button type="button" class="btn" @click="deleteRow">❌ Șterge rând</button>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn" @click="addColumnBefore">← Coloană înainte</button>
                <button type="button" class="btn" @click="addColumnAfter">→ Coloană după</button>
                <button type="button" class="btn" @click="deleteColumn">❌ Șterge coloană</button>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn" @click="mergeCells">🔗 Lipește celule</button>
                <button type="button" class="btn" @click="splitCell">✂️ Desparte celule</button>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn text-danger" @click="deleteTable">🗑️ Șterge tabel</button>
            </div>
        </div>
    </div>


    <!-- Tiptap Editor -->
    <div class="editor-container border rounded p-2 bg-white" :style="{ height: height }">
      <editor-content :editor="editor" />

      <!-- Floating Menu for Tables -->
      <!-- <div class="floating-menu" v-if="editor">
        <div v-show="editor.isActive('table')" class="floating-menu-content">
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" @click="addRowBefore">↑ Row Before</button>
            <button type="button" class="btn btn-outline-secondary" @click="addRowAfter">↓ Row After</button>
            <button type="button" class="btn btn-outline-secondary" @click="deleteRow">❌ Delete Row</button>
          </div>
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" @click="addColumnBefore">← Column Before</button>
            <button type="button" class="btn btn-outline-secondary" @click="addColumnAfter">→ Column After</button>
            <button type="button" class="btn btn-outline-secondary" @click="deleteColumn">❌ Delete Column</button>
          </div>
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" @click="mergeCells">🔗 Merge Cells</button>
            <button type="button" class="btn btn-outline-secondary" @click="splitCell">✂️ Split Cell</button>
          </div>
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-danger" @click="deleteTable">🗑️ Delete Table</button>
          </div>
        </div>
      </div> -->
    </div>


    <!-- Hidden Input for Form Submission -->
    <input
      type="hidden"
      :name="inputname"
      :value="editorContent"
    />
  </div>
</template>

<script>
import { Editor, EditorContent } from '@tiptap/vue-3';
import FloatingMenu from '@tiptap/extension-floating-menu';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import TextStyle from '@tiptap/extension-text-style';
import Color from '@tiptap/extension-color';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Table from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';

import { mergeCells, splitCell} from 'prosemirror-tables';

export default {
  components: { EditorContent },
  props: {
    inputvalue: {
      type: [String, Object],
      default: '',
    },
    inputname: {
      type: String,
      required: true,
    },
    height: {
        type: String,
        default: '400px', // Default editor height
    },
  },
  data() {
    return {
        editor: null,
        textColor: '#000000',
        isFullscreen: false,
    };
  },
  computed: {
    editorContent() {
      return JSON.stringify(this.editor?.getJSON() || {});
    },
    currentTextSize() {
        const selection = this.editor?.state.selection;
        if (!selection) return 'Default';

        const { from, to } = selection;
        let fontSize = 'Default';

        this.editor.state.doc.nodesBetween(from, to, (node) => {
        if (!node.marks) return;

        node.marks.forEach((mark) => {
            if (mark.type.name === 'textStyle' && mark.attrs.fontSize) {
            fontSize = mark.attrs.fontSize;
            }
        });
        });

        return fontSize;
    },
    currentTextColor() {
        if (!this.editor) return '#000000'; // Default color if the editor is not ready

        const { from, to } = this.editor.state.selection;
        let color = '#000000'; // Default text color

        this.editor.state.doc.nodesBetween(from, to, (node) => {
        if (!node.marks) return;

        node.marks.forEach((mark) => {
            if (mark.type.name === 'textStyle' && mark.attrs.color) {
            color = mark.attrs.color;
            }
        });
        });

        return color;
    },
    isBoldActive() {
      return this.editor?.isActive('bold');
    },
    isItalicActive() {
      return this.editor?.isActive('italic');
    },
    isUnderlineActive() {
      return this.editor?.isActive('underline');
    },
    isStrikeActive() {
      return this.editor?.isActive('strike');
    },
  },
  mounted() {
    let initialContent;

    try {
        if (!this.inputvalue || this.inputvalue === '') {
        // Fallback for empty inputvalue
        initialContent = { type: 'doc', content: [] };
        } else {
        // Parse JSON only if inputvalue is not empty
        initialContent = typeof this.inputvalue === 'string'
            ? JSON.parse(this.inputvalue)
            : this.inputvalue;
        }
    } catch (error) {
        console.error('Failed to parse JSON:', error);
        initialContent = { type: 'doc', content: [] };
    }

    this.editor = new Editor({
      extensions: [
        TextStyle.extend({
          addAttributes() {
            return {
              fontSize: {
                default: null,
                parseHTML: (element) => element.style.fontSize || null,
                renderHTML: (attributes) => {
                  if (!attributes.fontSize) {
                    return {};
                  }
                  return { style: `font-size: ${attributes.fontSize};` };
                },
              },
            };
          },
          addCommands() {
            return {
              setFontSize:
                (size) =>
                ({ chain }) => {
                  return chain()
                    .setMark('textStyle', { fontSize: size })
                    .run();
                },
            };
          },
        }),
        Color,
        StarterKit.configure({
            bold: true,
            italic: true,
        }),
        TextAlign.configure({
          types: ['heading', 'paragraph'],
        }),
        Link,
        Underline,
        Table.configure({
          resizable: true, // Enable column resizing
          allowTableNodeSelection: true, // Enable cell selection
        }),
        TableRow,
        TableHeader,
        TableCell.extend({
          addCommands() {
            return {
              mergeCells:
                () =>
                ({ state, dispatch }) => {
                  return mergeCells(state, dispatch);
                },
              splitCell:
                () =>
                ({ state, dispatch }) => {
                  return splitCell(state, dispatch);
                },
            };
          },
        }),
        // FloatingMenu.configure({
        //     element: document.querySelector('.floating-menu'),
        // }),
        FloatingMenu.configure({
          shouldShow: ({ editor, state }) => {
            const { $from } = state.selection;
            return $from.node($from.depth).type.name === 'table';
          },
        }),
      ],
      content: initialContent,
    });

    // Update the UI when the selection changes
    this.editor.on('selectionUpdate', () => {
      this.$forceUpdate();
    });
  },
  beforeUnmount() {
    this.editor.destroy();
  },
  methods: {
    toggleFullscreen() {
      this.isFullscreen = !this.isFullscreen;
      document.body.classList.toggle('editor-fullscreen-active', this.isFullscreen);
    },
    // Text Size
    setTextSize(size) {
      this.editor.chain().focus().setMark('textStyle', { fontSize: size }).run();
    },

    setTextColor(color) {
      this.textColor = color;
      this.editor.chain().focus().setMark('textStyle', { color: this.textColor }).run();
    },

    // Formatting
    toggleBold() {
      this.editor.chain().focus().toggleBold().run();
    },
    toggleItalic() {
      this.editor.chain().focus().toggleItalic().run();
    },
    toggleUnderline() {
      this.editor.chain().focus().toggleUnderline().run();
    },
    toggleStrike() {
      this.editor.chain().focus().toggleStrike().run();
    },

    // Text Alignment
    setTextAlign(alignment) {
      this.editor.chain().focus().setTextAlign(alignment).run();
    },

    // Lists
    toggleBulletList() {
      this.editor.chain().focus().toggleBulletList().run();
    },
    toggleOrderedList() {
      this.editor.chain().focus().toggleOrderedList().run();
    },

    // Links
    setLink() {
      const url = prompt('Enter a URL');
      if (url) {
        this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
      }
    },
    unsetLink() {
      this.editor.chain().focus().unsetLink().run();
    },

    // Undo/Redo
    undo() {
      this.editor.chain().focus().undo().run();
    },
    redo() {
      this.editor.chain().focus().redo().run();
    },

    // Table Management
    addTable() {
      this.editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
    },
    addColumnBefore() {
      this.editor.chain().focus().addColumnBefore().run();
    },
    addColumnAfter() {
      this.editor.chain().focus().addColumnAfter().run();
    },
    deleteColumn() {
      this.editor.chain().focus().deleteColumn().run();
    },
    addRowBefore() {
      this.editor.chain().focus().addRowBefore().run();
    },
    addRowAfter() {
      this.editor.chain().focus().addRowAfter().run();
    },
    deleteRow() {
      this.editor.chain().focus().deleteRow().run();
    },
    deleteTable() {
      this.editor.chain().focus().deleteTable().run();
    },

    // Table Manipulations
    mergeCells() {
      this.editor.chain().focus().mergeCells().run();
    },
    splitCell() {
      this.editor.chain().focus().splitCell().run();
    },
  },
};
</script>

<style>
.tiptap-container {
  border: 1px solid #ccc;
  border-radius: 2px;
  overflow: hidden;
  /* padding: 0px 10px; */
}

/* Toolbar Styling */
.editor-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  background-color: #f9f9f9;
  border-bottom: 1px solid #ccc;
  padding: 5px;
}

.editor-toolbar button {
  background-color: #fff;
  border: 1px solid #ddd;
  padding: 5px 10px;
  cursor: pointer;
  border-radius: 3px;
  font-size: 14px;
}

.editor-toolbar button:hover {
  background-color: #f0f0f0;
}

.editor-toolbar button.active {
  background-color: #007bff;
  color: white;
}

/* Editor Container */
.editor-container {
  position: relative;
  overflow-y: auto; /* Enable vertical scrolling when content exceeds max height */
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 8px;
  background: #fff;
}

/* Focus State for Better UX */
.editor-container:focus-within {
  outline: none;
  border: none;
}

/* Tiptap Content Area */
.editor-container .ProseMirror {
  outline: none; /* Remove outline focus border from editable area */
  border: none; /* Remove any border */
  min-height: 200px; /* Ensure enough space for cursor */
  padding: 5px; /* Add slight padding inside the editor */
}

/* Remove default focus styles on browser */
.editor-container .ProseMirror:focus {
  outline: none;
  border: none;
}

/* Remove paragraph margins for cleaner spacing */
.editor-container .ProseMirror p {
  margin: 0;
  min-height: 1em; /* Ensure space for cursor */
}

.editor-container p {
  margin: 2px 0; /* Adjust vertical spacing */
  line-height: 1.1; /* Adjust line height for better readability */
}

/* Table Styling */
.editor-container table {
  width: 100%;
  border-collapse: collapse;
  margin: 10px 0;
}
.editor-container table th,
.editor-container table td {
  border: 1px solid #ddd;
  padding: 5px;
  text-align: left;
}
.editor-container table th {
  background-color: #f5f5f5;
  font-weight: bold;
}

/* Highlight selected cells */
.editor-container .ProseMirror .selectedCell {
  background-color: rgba(0, 123, 255, 0.2); /* Light blue highlight */
  outline: 2px solid #007bff; /* Blue outline */
}

/* Ensure text size styles are respected */
.editor-container .ProseMirror span {
  font-size: inherit;
}

/* Ensure bold and fontSize are rendered correctly */
.editor-container .ProseMirror strong {
  font-weight: bold;
  font-size: inherit; /* Prevent overriding font-size */
}

/* Ensure italic does not override font size */
.editor-container .ProseMirror em {
  font-style: italic;
  font-size: inherit; /* Prevent overriding font-size */
}

/* Ensure underline does not override font size */
.editor-container .ProseMirror u {
  text-decoration: underline;
  font-size: inherit; /* Prevent overriding font-size */
}

/* Ensure strikethrough is styled properly */
.editor-container .ProseMirror s {
  text-decoration: line-through;
  font-size: inherit; /* Prevent strikethrough from overriding font size */
}

/* Floating Menu Styling */
.floating-menu {
  position: absolute;
  z-index: 1000;
  background: #fff;
  border: 1px solid #ddd;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding: 5px;
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  gap: 5px;
  top: -50px; /* Position it above the selected table */
  left: 50%;
  transform: translateX(-50%);
}

.floating-menu .btn {
  font-size: 12px;
}

/* Table Menu Styling */
.table-menu {
  position: sticky;
  top: 0;
  background: #fff;
  border-bottom: 1px solid #ddd;
  z-index: 1000;
  display: flex;
  gap: 8px;
  padding: 8px;
  margin-bottom: 4px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Smaller max height on mobile */
@media (max-width: 768px) {
  .editor-container {
    max-height: 300px;
  }
}

/* Fullscreen Mode Styling */
.editor-fullscreen-active {
  overflow: hidden;
}

.editor-fullscreen-active .tiptap-container {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #fff;
  z-index: 9999;
  display: flex;
  flex-direction: column;
}

.editor-fullscreen-active .editor-toolbar {
  z-index: 10000;
  background: #fff;
  border-bottom: 1px solid #ddd;
}

.editor-fullscreen-active .editor-container {
  flex-grow: 1;
  max-height: none;
  overflow-y: auto;
  border: none;
  border-radius: 0;
}

.editor-toolbar .form-label {
  margin-right: 5px;
  font-size: 14px;
  align-self: center;
}

.editor-toolbar .form-control-color {
  border: none;
  padding: 0;
  height: 30px;
  width: 30px;
  cursor: pointer;
  background: transparent;
}

.current-color-display {
  display: inline-block;
  width: 20px;
  height: 20px;
  margin-left: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
</style>
