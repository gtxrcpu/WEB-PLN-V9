import os
import re

models = ['Apar.php', 'Apat.php', 'Apab.php', 'FireAlarm.php', 'BoxHydrant.php', 'RumahPompa.php', 'P3k.php']
path = 'app/Models/'

for model in models:
    file_path = os.path.join(path, model)
    if not os.path.exists(file_path):
        continue
    
    with open(file_path, 'r') as f:
        content = f.read()
    
    module_name = model.replace('.php', '').lower()
    if module_name == 'firealarm': module_name = 'fire-alarm'
    if module_name == 'boxhydrant': module_name = 'box-hydrant'
    if module_name == 'rumahpompa': module_name = 'rumah-pompa'
    
    # We want to replace getQrUrlAttribute() entirely.
    # It might have json_encode, or it might be badly merged like in Apat.php
    
    # We will just write a custom getQrUrlAttribute
    new_method = '''    public function getQrUrlAttribute(): string
    {
         = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', ['module' => \'''' + module_name + '''\', 'id' => ->id]);
        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri();
    }'''

    # For Apat.php which has a corrupted method, we can fix it specifically or use a regex to replace the function definition.
    # Actually, a regex pattern to replace public function getQrUrlAttribute(): string { ... } 
    pattern = re.compile(r'public function getQrUrlAttribute\(\): string\s*\{.*?(?:return|try).*?\}(?=\s*public function|\s*\Z)', re.DOTALL)
    
    if pattern.search(content):
        # We need to be careful with Apat.php because it's missing the generateQrSvg signature.
        pass

