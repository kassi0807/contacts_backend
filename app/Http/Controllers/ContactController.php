<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ContactController extends Controller {

    function index(){
        $page = Contact::latest()->paginate(12);
        return response()->json($page, 200);
    }

    function search(Request $request){
        $wildcard = "%".$request->keyword."%";
        $results = Contact::where('name', 'like', $wildcard)
        ->orWhere('company', 'like', $wildcard)
        ->orWhere('address', 'like', $wildcard)
        ->orWhere('email', 'like', $wildcard)
        ->orWhere('phone', 'like', $wildcard)->get();
        return response()->json($results, 200);
    }

    function downloadPhoto(string $photo, int $id){
        try{
            $contact = Contact::findOrFail($id); 
            if($contact->photo){
                $path = storage_path("app/public/photos/".$contact->photo);
                return response()->download($path, 200);
            } else 
                return response()->json(["errors" => "photo not found"], 404);


        }catch(ModelNotFoundException $exception){
            return response()->json([], 404);
        }
        catch(FileNotFoundException $e){
            return response()->json([], 404);
        }
    }

    function uploadPhoto(Request $request, int $id){
        try{
            // validate input
            $validator =  Validator::make($request->all(), [
                'photo' => "required|image"
            ]);
            if($validator->fails())
            throw new ValidationException($validator);
            
            $contact = Contact::findOrFail($id);
            $this->removePhoto($contact->photo);

            // save and update contact
            $photo = $request->photo ;
            $photo->storeAs('public/photos', $photo->hashName());
            $contact->update(["photo" => $photo->hashName()]);

            // return new photo
            return response()->json(["photo" => $contact->photo], 201);

        }catch(ModelNotFoundException $exception){
            return response()->json([], 404);
        }
        catch(\Exception $e){
            return response()->json([$e], 500);
        }
    }


    function store(Request $request){
        $validator = $this->getValidatorOf($request);
        if($validator->fails())
            throw new ValidationException($validator);
        else {
            $contact = Contact::create($validator->validated());
            return response()->json(['id' => $contact->id], 201);
        }
    }



    function update(Request $request, int $id){
        $validator = $this->getValidatorOf($request);
        if($validator->fails())
            throw new ValidationException($validator);
        else try {
                $contact = Contact::findOrFail($id);
                $contact->update($validator->validated());
                return response()->json([], 204);
        } catch(ModelNotFoundException $exception){
            return response()->json([], 404);
        } catch(\Exception $exception){
            return response()->json([], 500);
        } 
    }

    function destroy(int $id){
        try {
            $contact = Contact::findOrFail($id);
            $this->removePhoto($contact->photo);
            $contact->delete();
            return response()->json([], 200);
        } catch(ModelNotFoundException $exception){
            return response()->json([], 404);
        } catch(\Exception $exception){
                return response()->json([], 500);
        }
    }

    private function removePhoto($photo){
        $storage = Storage::disk('local');
        $path = 'public/photos/'. $photo;
        if($storage->exists($path))
            $storage->delete($path);
    }

    private function getValidatorOf(Request $request){ 
        return Validator::make($request->all(), [
            'name' => 'required|string|between:3,30',
            'company' => 'required|string|between:3,30',
            'address' => 'required|between:3,50',
            'email' => 'required|email:strict',
            'phone' => 'required|string'
        ]);
    }
}
