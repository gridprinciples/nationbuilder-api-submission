<?php

namespace App\Http\Controllers\NationBuilder;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PeopleController extends Controller
{
    /**
     * Show the list of all people.
     */
    public function index()
    {
        $people = app()->nationbuilder->get('people');

        return view('nationbuilder.people.index', compact('people'));
    }

    /**
     * Show the "new person" form
     */
    public function create()
    {
        return view('nationbuilder.people.create');
    }

    /**
     * Save the "new person" form
     */
    public function store(Request $request)
    {
        $validated = collect($request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|string|max:255',
        ]));

        app()->nationbuilder->post('people', [
            'person' => $validated->only('name', 'first_name', 'last_name'),
        ]);

        return redirect()->route('nationbuilder.people.index');
    }

    /**
     * Show a person
     */
    public function show(string $personID)
    {
        $person = $this->findPersonOrAbort($personID);

        return view('nationbuilder.people.show', compact('person'));
    }

    /**
     * Show the "edit person" form
     */
    public function edit(string $personID)
    {
        $person = $this->findPersonOrAbort($personID);

        return view('nationbuilder.people.edit', compact('person'));
    }

    /**
     * Save the "edit person" form
     */
    public function update(Request $request, string $personID)
    {
        $this->findPersonOrAbort($personID);

        $validated = collect($request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|string|max:255',
        ]));

        app()->nationbuilder->put('people/' . $personID, [
            'person' => $validated->only('name', 'first_name', 'last_name'),
        ]);

        return redirect()->route('nationbuilder.people.index');
    }
    
    /**
     * Delete a person
     */
    public function destroy(string $personID)
    {
        $this->findPersonOrAbort($personID);

        app()->nationbuilder->delete('people/' . $personID);

        return redirect()->route('nationbuilder.people.index');
    }

    /**
     * Find a person using the API, else throw a 404.
     */
    protected function findPersonOrAbort(string $personID)
    {
        $found = app()->nationbuilder->get('people/' . $personID);

        if(! $found) {
            return abort(404);
        }

        return $found;
    }
}
