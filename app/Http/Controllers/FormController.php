<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cars;
use App\Models\Offer;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;




class FormController extends Controller
{
    public function submitCarForm(Request $request)
    {
        // Formdan gelen verileri al
        $formData = $request->all();

        $users = User::all();

        Cars::create($formData);
        return view('arac',compact('users'))->with('success', 'Kayıt başarıyla oluşturuldu!');

        // Başarılı bir şekilde ekledikten sonra aynı sayfaya dön
        return back()->with('success', 'Kayıt başarıyla oluşturuldu!');
    }

        public function listCarForm()
        {

            $listrequests = Cars::all();

            // İlk araç talebini çek
            $carRequest = Cars::first();

            return view('admin.listrequest', compact('listrequests', 'carRequest', 'users'));
        }

    public function showCarForm($id)
    {
        $listrequests = Cars::all();
        $carRequest = Cars::findOrFail($id);
        $comments = Comment::where('car_request_id', $id)->get();
        $teklifler = Offer::where('car_request_id', $id)->get(); // Tüm teklifleri al

        return view('admin.showrequest', compact('carRequest', 'listrequests', 'comments', 'teklifler'));
    }

    public function editCarForm($id)
    {
        $carRequest = Cars::findOrFail($id);

        return view('admin.aracedit', compact('carRequest'));
    }

    public function updateCarForm(Request $request, $id)
    {
        $validatedData = $request->validate([
            // Validasyon kurallarını buraya ekleyin
        ]);

        Cars::where('id', $id)->update($validatedData);

        return redirect()->route('admin.showrequest', $id)->with('success', 'Araç talebi başarıyla güncellendi!');
    }

    public function deleteCarForm($id)
    {
        Cars::findOrFail($id)->delete();

        return redirect()->route('admin.listrequest')->with('success', 'Araç talebi başarıyla silindi!');
    }

        public function addComment(Request $request, $carRequestId)
        {
            $request->validate([
                'comment' => 'required|string',
            ]);

            Comment::create([
                'car_request_id' => $carRequestId,
                'user_name' => auth()->user()->name, // Ya da kullanıcı adını nereden alıyorsanız ona göre düzenleyin
                'email' => auth()->user()->email, // Ya da kullanıcı adını nereden alıyorsanız ona göre düzenleyin
                'comment' => $request->input('comment'),
            ]);

            return back()->with('success', 'Mesaj başarıyla eklendi!');
        }

            public function teklifKaydet(Request $request, $carRequestId)
            {
                // Formdan gelen verileri al
                $teklifData = $request->only(['teklif_baslik', 'genel_bilgiler', 'fiyat','doviz']);

                // Veritabanına kaydet
                Offer::create([
                    'car_request_id' => $carRequestId,
                    'teklif_baslik' => $teklifData['teklif_baslik'],
                    'genel_bilgiler' => $teklifData['genel_bilgiler'],
                    'fiyat' => $teklifData['fiyat'],
                    'doviz' => $teklifData['doviz'],
                ]);

                return redirect()->back()->with('success', 'Teklif başarıyla eklendi.');
            }

            public function onaylaTeklif($teklifId)
            {
                $teklif = Offer::findOrFail($teklifId);

                // Eğer teklif zaten onaylanmamışsa, onay durumunu güncelle
                if ($teklif->onay_durumu != 'onaylandi') {
                    $teklif->update(['onay_durumu' => 'onaylandi']);
                }

                return Redirect::back()->with('success', 'Teklif başarıyla onaylandı.');
            }
}
