<?php

namespace App\Http\Controllers;

use App\Models\NamedLink;
use App\Models\Notebook;
use App\Models\StudentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NamedLinkController extends Controller
{
    /**
     * Список всех именных ссылок автора текущей тетради.
     */
    public function index(Notebook $notebook)
    {
        // Получаем все ссылки для данной тетради
        $links = $notebook->namedLinks()->latest()->paginate(15);

        return view('named_links.index', compact('notebook', 'links'));
    }

    /**
     * Показ формы создания новой именной ссылки.
     */
    public function create(Notebook $notebook)
    {
        return view('named_links.create', compact('notebook'));
    }

    /**
     * Сохранение новой именной ссылки.
     */
    public function store(Request $request, Notebook $notebook)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Генерируем токен
        $token = Str::random(32);

        $link = NamedLink::create([
            'notebook_id' => $notebook->id,
            'token'       => $token,
            'title'       => $data['title'],
        ]);

        return redirect()
            ->route('named_links.index', $notebook)
            ->with('status', "Ссылка «{$link->title}» создана: " . route('named_links.view', $link->token));
    }

    /**
     * Показ формы редактирования существующей именной ссылки.
     */
    public function edit(Notebook $notebook, NamedLink $namedLink)
    {
        // Убеждаемся, что ссылка принадлежит тетради
        abort_unless($namedLink->notebook_id === $notebook->id, 404);

        return view('named_links.edit', compact('notebook', 'namedLink'));
    }

    /**
     * Обновление данных именной ссылки.
     */
    public function update(Request $request, Notebook $notebook, NamedLink $namedLink)
    {
        abort_unless($namedLink->notebook_id === $notebook->id, 404);

        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $namedLink->update([
            'title' => $data['title'],
        ]);

        return redirect()
            ->route('named_links.index', $notebook)
            ->with('status', "Ссылка обновлена: {$namedLink->title}");
    }

    /**
     * Удаление именной ссылки.
     */
    public function destroy(Notebook $notebook, NamedLink $namedLink)
    {
        abort_unless($namedLink->notebook_id === $notebook->id, 404);

        $namedLink->delete();

        return redirect()
            ->route('named_links.index', $notebook)
            ->with('status', 'Ссылка удалена');
    }

    /**
     * Просмотр копии тетради по токену (публичный доступ).
     */
    public function view(string $token)
    {
        $link = NamedLink::where('token', $token)->firstOrFail();
        $notebook = $link->notebook;

        // Здесь мы рендерим только поля для ответов (как в named_links.view)
        return view('named_links.view', compact('link', 'notebook'));
    }
    
    /**
     * Приём и автосохранение ответов ученика
     */
   public function submit(Request $request, string $token)
{
    $link = NamedLink::where('token', $token)->firstOrFail();
    $data = $request->validate([
        'responses'   => 'required|array',
        'responses.*' => 'nullable|string',
    ]);

    $results = [];
    foreach ($data['responses'] as $fieldId => $input) {
        $field = ResponseField::findOrFail($fieldId);
        $isCorrect = null;

        if ($field->field_type === 'text' && $field->correct_answers) {
            $corrects = array_map('mb_strtolower', $field->correct_answers);
            $user    = mb_strtolower(trim($input));
            $isCorrect = in_array($user, $corrects, true);
        }

        StudentResponse::updateOrCreate(
            [
                'named_link_id'     => $link->id,
                'response_field_id' => $fieldId,
            ],
            [
                'user_input' => $input,
                'is_correct' => $isCorrect,
            ]
        );

        $results[$fieldId] = $isCorrect;
    }

    return response()->json(['results' => $results]);
}

}
<?php

namespace App\Http\Controllers;

use App\Models\NamedLink;
use App\Models\Notebook;
use App\Models\StudentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NamedLinkController extends Controller
{
    /**
     * Список всех именных ссылок автора текущей тетради.
     */
    public function index(Notebook $notebook)
    {
        // Получаем все ссылки для данной тетради
        $links = $notebook->namedLinks()->latest()->paginate(15);

        return view('named_links.index', compact('notebook', 'links'));
    }

    /**
     * Показ формы создания новой именной ссылки.
     */
    public function create(Notebook $notebook)
    {
        return view('named_links.create', compact('notebook'));
    }

    /**
     * Сохранение новой именной ссылки.
     */
    public function store(Request $request, Notebook $notebook)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Генерируем токен
        $token = Str::random(32);

        $link = NamedLink::create([
            'notebook_id' => $notebook->id,
            'token'       => $token,
            'title'       => $data['title'],
        ]);

        return redirect()
            ->route('named_links.index', $notebook)
            ->with('status', "Ссылка «{$link->title}» создана: " . route('named_links.view', $link->token));
    }

    /**
     * Показ формы редактирования существующей именной ссылки.
     */
    public function edit(Notebook $notebook, NamedLink $namedLink)
    {
        // Убеждаемся, что ссылка принадлежит тетради
        abort_unless($namedLink->notebook_id === $notebook->id, 404);

        return view('named_links.edit', compact('notebook', 'namedLink'));
    }

    /**
     * Обновление данных именной ссылки.
     */
    public function update(Request $request, Notebook $notebook, NamedLink $namedLink)
    {
        abort_unless($namedLink->notebook_id === $notebook->id, 404);

        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $namedLink->update([
            'title' => $data['title'],
        ]);

        return redirect()
            ->route('named_links.index', $notebook)
            ->with('status', "Ссылка обновлена: {$namedLink->title}");
    }

    /**
     * Удаление именной ссылки.
     */
    public function destroy(Notebook $notebook, NamedLink $namedLink)
    {
        abort_unless($namedLink->notebook_id === $notebook->id, 404);

        $namedLink->delete();

        return redirect()
            ->route('named_links.index', $notebook)
            ->with('status', 'Ссылка удалена');
    }

    /**
     * Просмотр копии тетради по токену (публичный доступ).
     */
    public function view(string $token)
    {
        $link = NamedLink::where('token', $token)->firstOrFail();
        $notebook = $link->notebook;

        // Здесь мы рендерим только поля для ответов (как в named_links.view)
        return view('named_links.view', compact('link', 'notebook'));
    }
    
    /**
     * Приём и автосохранение ответов ученика
     */
   public function submit(Request $request, string $token)
{
    $link = NamedLink::where('token', $token)->firstOrFail();
    $data = $request->validate([
        'responses'   => 'required|array',
        'responses.*' => 'nullable|string',
    ]);

    $results = [];
    foreach ($data['responses'] as $fieldId => $input) {
        $field = ResponseField::findOrFail($fieldId);
        $isCorrect = null;

        if ($field->field_type === 'text' && $field->correct_answers) {
            $corrects = array_map('mb_strtolower', $field->correct_answers);
            $user    = mb_strtolower(trim($input));
            $isCorrect = in_array($user, $corrects, true);
        }

        StudentResponse::updateOrCreate(
            [
                'named_link_id'     => $link->id,
                'response_field_id' => $fieldId,
            ],
            [
                'user_input' => $input,
                'is_correct' => $isCorrect,
            ]
        );

        $results[$fieldId] = $isCorrect;
    }

    return response()->json(['results' => $results]);
}

}
