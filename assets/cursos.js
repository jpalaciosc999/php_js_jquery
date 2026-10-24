'use strict';
$(function () {
    const fields = $('#curso_fields');
    const status = $('#course-status');
    let nextId = fields.find('.course-row').length;

    function autocomplete(input) {
        input.autocomplete({
            minLength: 1,
            delay: 200,
            source: function (request, response) {
                $.ajax({ url: 'cursos.php', data: { term: request.term }, dataType: 'json' })
                    .done(function (data) { status.text(''); response(data); })
                    .fail(function (xhr) {
                        response([]);
                        if (xhr.responseText && xhr.responseText.includes('Ingreso al sistema')) {
                            window.location.assign('login.php');
                            return;
                        }
                        status.text('No se pudieron consultar las sugerencias. Intente nuevamente.');
                    });
            }
        });
    }
    autocomplete(fields.find('.cursos'));
    $('#addCurso').on('click', function () {
        if (fields.find('.course-row').length >= 100) {
            status.text('Puede ingresar hasta 100 cursos.');
            return;
        }
        const id = nextId++;
        const row = $(`<div class="course-row border rounded p-3 mb-3">
            <div class="d-flex gap-2 align-items-center mb-2">
                <label for="anio-${id}">Año</label>
                <input type="number" min="1900" max="2100" step="1" class="form-control w-auto" id="anio-${id}" name="cursos[${id}][anio]">
                <button type="button" class="btn btn-outline-danger remove-course" aria-label="Quitar curso">−</button>
            </div>
            <label for="curso-${id}" class="form-label">Nombre del curso</label>
            <input type="text" class="form-control cursos" maxlength="50" id="curso-${id}" name="cursos[${id}][nombre]" autocomplete="off">
        </div>`);
        fields.append(row);
        autocomplete(row.find('.cursos'));
        row.find('input[type="number"]').trigger('focus');
        status.text('');
    });
    fields.on('click', '.remove-course', function () {
        $(this).closest('.course-row').remove();
        status.text('');
    });
});
