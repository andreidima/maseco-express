<template>
  <div class="tiptap-container">
    <!-- Toolbar Menu -->
    <div class="editor-toolbar bg-light border rounded p-2 mb-2">

        <div class="btn-toolbar mb-2" role="toolbar">
            <!-- Undo/Redo -->
            <div class="btn-group me-3" role="group">
                <button type="button" @click="undo" title="Undo"><i class="fa-solid fa-rotate-left"></i></button>
                <button type="button" @click="redo" title="Redo"><i class="fa-solid fa-rotate-right"></i></button>
            </div>

            <!-- Text Size Dropdown -->
            <div class="btn-group me-3">
                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    Text Size: {{ currentTextSize }}
                </button>
                <ul class="dropdown-menu">
                    <button type="button" class="btn" @click="setTextSize('12px')">12px</button>
                    <button type="button" class="btn" @click="setTextSize('14px')">14px</button>
                    <button type="button" class="btn" @click="setTextSize('16px')">16px</button>
                    <button type="button" class="btn" @click="setTextSize('18px')">18px</button>
                    <button type="button" class="btn" @click="setTextSize('20px')">20px</button>
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
            <div class="btn-group me-3">
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
    </div>

    <!-- Tiptap Editor -->
    <div class="editor-container">
      <editor-content :editor="editor" />
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
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import TextStyle from '@tiptap/extension-text-style';
// import { CSSProperties } from '@tiptap/extension-text-style';
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
  },
  data() {
    return {
      editor: null,
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
              selectCell:
                () =>
                ({ state, dispatch }) => {
                  return selectCell(state, dispatch);
                },
            };
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
    // Text Size
    setTextSize(size) {
      this.editor.chain().focus().setMark('textStyle', { fontSize: size }).run();
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

/* Editor Area */
.editor-container {
  border: none; /* Remove any border */
  outline: none; /* Remove outline focus border */
  padding: 10px;
  min-height: 200px;
  overflow-y: auto;
  background-color: #fff; /* Ensure clear background */
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
</style>
