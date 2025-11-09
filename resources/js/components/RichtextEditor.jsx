import { Editor } from "@tinymce/tinymce-react"
import React, { useRef } from "react"

const RichtextEditor = ({ value = null, form, name }) => {
  const editorRef = useRef(null)

  return (
    <Editor
      tinymceScriptSrc={"../../../tinymce/tinymce.min.js"}
      onInit={(evt, editor) => (editorRef.current = editor)}
      value={value}
      init={{
        height: 500,
        menubar: false,
        plugins: [
          "advlist",
          "autolink",
          "lists",
          "link",
          "image",
          "charmap",
          "anchor",
          "searchreplace",
          "visualblocks",
          "code",
          "fullscreen",
          "insertdatetime",
          "media",
          "table",
          "preview",
          "help",
          "wordcount",
          "emoticons",
          "code",
        ],
        toolbar: `undo redo | blocks | fontselect sizeselect fontsizeselect | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | table | preview | image | link | emoticons | code | fullscreen `,
        // content_style:
        //   "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",
        content_css: ["https://fonts.googleapis.com/css?family=Gugi"],
        font_formats:
          "Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; AkrutiKndPadmini=Akpdmi-n",
        fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
      }}
      onEditorChange={(content, editor) => {
        form.setFieldValue(name, content)
      }}
    />
  )
}

export default RichtextEditor
