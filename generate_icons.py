import os
from PIL import Image, ImageDraw, ImageFont

def create_base_icon(size=1024, is_foreground_only=False, is_round=False):
    if is_foreground_only:
        img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    else:
        img = Image.new('RGBA', (size, size), (2, 6, 23, 255))
        glow = Image.new('RGBA', (size, size), (0, 0, 0, 0))
        g_draw = ImageDraw.Draw(glow)
        center = size // 2
        for r in range(size // 2, 0, -8):
            alpha = int(45 * (1 - r / (size // 2)))
            g_draw.ellipse(
                [center - r, center - r, center + r, center + r],
                fill=(16, 185, 129, alpha)
            )
        img = Image.alpha_composite(img, glow)

    draw = ImageDraw.Draw(img)

    if is_foreground_only:
        box_size = int(size * 0.60)
    else:
        box_size = int(size * 0.68)
        
    left = (size - box_size) // 2
    top = (size - box_size) // 2
    right = left + box_size
    bottom = top + box_size
    radius = int(box_size * 0.28)

    squircle = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    s_draw = ImageDraw.Draw(squircle)
    s_draw.rounded_rectangle([left, top, right, bottom], radius=radius, fill=(16, 185, 129, 255))

    grad = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    g_draw = ImageDraw.Draw(grad)
    for y in range(top, bottom):
        ratio = (y - top) / float(box_size)
        r = int(16 * (1 - ratio) + 13 * ratio)
        g = int(185 * (1 - ratio) + 148 * ratio)
        b = int(129 * (1 - ratio) + 136 * ratio)
        g_draw.line([(left, y), (right, y)], fill=(r, g, b, 255))

    mask = squircle.split()[3]
    img.paste(grad, (0, 0), mask)

    s_draw = ImageDraw.Draw(img)
    s_draw.rounded_rectangle(
        [left + 2, top + 2, right - 2, bottom - 2],
        radius=radius - 2,
        outline=(52, 211, 153, 140),
        width=int(size * 0.01)
    )

    font_size = int(box_size * 0.68)
    font_path = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
    if not os.path.exists(font_path):
        font_path = "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf"
    
    font = ImageFont.truetype(font_path, font_size)

    bbox = font.getbbox("F")
    text_w = bbox[2] - bbox[0]
    text_h = bbox[3] - bbox[1]
    
    text_x = left + (box_size - text_w) // 2 - bbox[0] - int(size * 0.01)
    text_y = top + (box_size - text_h) // 2 - bbox[1]

    draw.text((text_x, text_y), "F", fill=(2, 6, 23, 255), font=font)

    dot_radius = int(box_size * 0.08)
    dot_center_x = right - int(box_size * 0.16)
    dot_center_y = top + int(box_size * 0.16)
    draw.ellipse(
        [dot_center_x - dot_radius, dot_center_y - dot_radius,
         dot_center_x + dot_radius, dot_center_y + dot_radius],
        fill=(251, 191, 36, 255),
        outline=(2, 6, 23, 200),
        width=int(size * 0.008)
    )

    if is_round and not is_foreground_only:
        round_mask = Image.new('L', (size, size), 0)
        r_draw = ImageDraw.Draw(round_mask)
        r_draw.ellipse([0, 0, size, size], fill=255)
        round_img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
        round_img.paste(img, (0, 0), round_mask)
        return round_img

    return img

def create_splash_screen(w, h, base_logo):
    splash = Image.new('RGBA', (w, h), (2, 6, 23, 255))
    logo_size = int(min(w, h) * 0.35)
    logo_resized = base_logo.resize((logo_size, logo_size), Image.Resampling.LANCZOS)
    x = (w - logo_size) // 2
    y = (h - logo_size) // 2
    splash.paste(logo_resized, (x, y), logo_resized)
    return splash

def generate_all():
    base_full = create_base_icon(1024, is_foreground_only=False, is_round=False)
    base_round = create_base_icon(1024, is_foreground_only=False, is_round=True)
    base_fg = create_base_icon(1024, is_foreground_only=True, is_round=False)

    os.makedirs("public/images", exist_ok=True)
    base_full.resize((512, 512), Image.Resampling.LANCZOS).save("public/icon.png", "PNG")
    base_full.resize((192, 192), Image.Resampling.LANCZOS).save("public/images/logo.png", "PNG")
    base_full.resize((64, 64), Image.Resampling.LANCZOS).save("public/favicon.ico", "ICO")

    android_res = "android/app/src/main/res"
    sizes = {
        "mipmap-mdpi": (48, 108),
        "mipmap-hdpi": (72, 162),
        "mipmap-xhdpi": (96, 216),
        "mipmap-xxhdpi": (144, 324),
        "mipmap-xxxhdpi": (192, 432),
    }

    for folder, (app_size, fg_size) in sizes.items():
        dir_path = os.path.join(android_res, folder)
        os.makedirs(dir_path, exist_ok=True)
        base_full.resize((app_size, app_size), Image.Resampling.LANCZOS).save(os.path.join(dir_path, "ic_launcher.png"), "PNG")
        base_round.resize((app_size, app_size), Image.Resampling.LANCZOS).save(os.path.join(dir_path, "ic_launcher_round.png"), "PNG")
        base_fg.resize((fg_size, fg_size), Image.Resampling.LANCZOS).save(os.path.join(dir_path, "ic_launcher_foreground.png"), "PNG")

    # Generate splash screens
    splash_sizes = {
        "drawable": (480, 800),
        "drawable-port-mdpi": (320, 480),
        "drawable-port-hdpi": (480, 800),
        "drawable-port-xhdpi": (720, 1280),
        "drawable-port-xxhdpi": (960, 1600),
        "drawable-port-xxxhdpi": (1280, 1920),
        "drawable-land-mdpi": (480, 320),
        "drawable-land-hdpi": (800, 480),
        "drawable-land-xhdpi": (1280, 720),
        "drawable-land-xxhdpi": (1600, 960),
        "drawable-land-xxxhdpi": (1920, 1280),
    }

    for folder, (w, h) in splash_sizes.items():
        dir_path = os.path.join(android_res, folder)
        os.makedirs(dir_path, exist_ok=True)
        create_splash_screen(w, h, base_full).save(os.path.join(dir_path, "splash.png"), "PNG")

    print("✅ All icons and splash screens generated successfully!")

if __name__ == "__main__":
    generate_all()

