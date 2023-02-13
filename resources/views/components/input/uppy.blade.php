<div
    x-data
    x-init="
        onUploadSuccess = (elForUploadedFiles) =>
          (file, response) => {
            const url = response.uploadURL;
            const fileName = file.name;

            const uploadedFileData = JSON.stringify(response.body);

            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = url;
            a.target = '_blank';
            a.appendChild(document.createTextNode(fileName));
            li.appendChild(a);

            document.querySelector(elForUploadedFiles).appendChild(li);

            var inputElementUrlUploadFile = document.getElementById('{{ $hiddenField }}');
            inputElementUrlUploadFile.value = url;
            inputElementUrlUploadFile.dispatchEvent(new Event('input'));

            {{ $extraJSForOnUploadSuccess }}
          };

        const uppyUpload{{ $hiddenField }} = new Uppy({{ $options }});

        uppyUpload{{ $hiddenField }}
          .use(DragDrop, {{ $dragDropOptions }})
          .use(AwsS3Multipart, {
              companionUrl: '/',
              companionHeaders:
              {
                  'X-CSRF-TOKEN': window.csrfToken,
              },
          })
          .use(StatusBar, {{ $statusBarOptions }})
          .on('upload-success', onUploadSuccess('.{{ $uploadElementClass }} .uploaded-files ol'));
    "
>
    <section class="{{ $uploadElementClass }}">
      <div class="for-DragDrop" x-ref="input"></div>

      <div class="for-ProgressBar"></div>

      <div class="uploaded-files">
        <h5>{{ __('Uploaded file:') }}</h5>
        <ol></ol>
      </div>
    </section>
</div>
