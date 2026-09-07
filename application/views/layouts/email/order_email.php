<!doctype html>
<html lang="ro">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta 
        name="format-detection"
        content="telephone=no"
    >

    <title>Comanda Vizaje-Nica #{order_id}</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #eeeeee;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        img {
            display: block;
            border: 0;
        }

        @media only screen and (max-width: 620px) {
            .wrapper {
                width: 100% !important;
            }

            .spacing {
                padding-left: 22px !important;
                padding-right: 22px !important;
            }

            .customer-cell {
                display: block !important;
                width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .title {
                font-size: 28px !important;
                line-height: 35px !important;
            }
        }
    </style>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #eeeeee;
    "
>
<table
    role="presentation"
    width="100%"
    border="0"
    cellspacing="0"
    cellpadding="0"
    style="
        width: 100%;
        background-color: #eeeeee;
    "
>
    <tr>
        <td
            align="center"
            style="
                padding: 28px 12px;
            "
        >
            <table
                role="presentation"
                class="wrapper"
                width="600"
                border="0"
                cellspacing="0"
                cellpadding="0"
                style="
                    width: 600px;
                    max-width: 600px;
                    background-color: #ffffff;
                    font-family: Arial, Helvetica, sans-serif;
                "
            >

                <!-- Logo -->
                <tr>
                    <td
                        align="center"
                        class="spacing"
                        style="
                            padding: 30px 40px;
                            border-bottom: 1px solid #eeeeee;
                        "
                    >
                        <a
                            href="https://{site}"
                            target="_blank"
                            style="
                                display: inline-block;
                                font-family: Georgia, serif;
                                font-size: 22px;
                                letter-spacing: 2px;
                                color: #111111;
                                text-decoration: none;
                            "
                        >
                            VIZAJE-NICA
                        </a>
                    </td>
                </tr>

                <!-- Success -->
                <tr>
                    <td
                        align="center"
                        class="spacing"
                        style="
                            padding: 42px 40px 10px;
                        "
                    >
                        <table
                            role="presentation"
                            cellspacing="0"
                            cellpadding="0"
                        >
                            <tr>
                                <td
                                    align="center"
                                    valign="middle"
                                    width="48"
                                    height="48"
                                    style="
                                        width: 48px;
                                        height: 48px;
                                        border-radius: 50%;
                                        background-color: #111111;
                                        color: #ffffff;
                                        font-size: 22px;
                                        line-height: 48px;
                                    "
                                >
                                    ✓
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Heading -->
                <tr>
                    <td
                        align="center"
                        class="spacing"
                        style="
                            padding: 12px 40px 38px;
                        "
                    >
                        <p
                            style="
                                margin: 0 0 9px;
                                font-size: 11px;
                                line-height: 17px;
                                letter-spacing: 1.4px;
                                text-transform: uppercase;
                                color: #999999;
                            "
                        >
                            Comandă înregistrată
                        </p>

                        <h1
                            class="title"
                            style="
                                margin: 0;
                                font-family: Georgia, 'Times New Roman', serif;
                                font-size: 34px;
                                line-height: 42px;
                                font-weight: 400;
                                color: #111111;
                            "
                        >
                            Vă mulțumim pentru comandă
                        </h1>

                        <p
                            style="
                                margin: 18px auto 0;
                                max-width: 450px;
                                font-size: 14px;
                                line-height: 22px;
                                color: #666666;
                            "
                        >
                            Salut,
                            <strong style="color: #111111;">
                                {recipient_name}
                            </strong>.

                            Am primit comanda
                            <strong style="color: #111111;">
                                #{order_id}
                            </strong>.

                            Un operator Vizaje-Nica vă va contacta
                            dacă sunt necesare informații suplimentare.
                        </p>
                    </td>
                </tr>

                <!-- Status -->
                <tr>
                    <td
                        class="spacing"
                        style="
                            padding: 0 40px 34px;
                        "
                    >
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            style="
                                width: 100%;
                                background-color: #f7f7f7;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 20px 22px;
                                    "
                                >
                                    <p
                                        style="
                                            margin: 0 0 4px;
                                            font-size: 10px;
                                            line-height: 15px;
                                            letter-spacing: 0.9px;
                                            text-transform: uppercase;
                                            color: #999999;
                                        "
                                    >
                                        Data comenzii
                                    </p>

                                    <strong
                                        style="
                                            font-size: 14px;
                                            line-height: 20px;
                                            color: #111111;
                                        "
                                    >
                                        {added}
                                    </strong>
                                </td>

                                <td
                                    align="right"
                                    style="
                                        padding: 20px 22px;
                                    "
                                >
                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 8px 12px;
                                            border-radius: 16px;
                                            background-color: #eaf7ed;
                                            font-size: 11px;
                                            line-height: 16px;
                                            font-weight: 600;
                                            color: #237a3b;
                                        "
                                    >
                                        Comandă înregistrată
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Customer -->
                <tr>
                    <td
                        class="spacing"
                        style="
                            padding: 0 40px 16px;
                        "
                    >
                        <p
                            style="
                                margin: 0;
                                font-size: 11px;
                                line-height: 17px;
                                letter-spacing: 1px;
                                text-transform: uppercase;
                                color: #999999;
                            "
                        >
                            Date client
                        </p>

                        <h2
                            style="
                                margin: 6px 0 0;
                                font-family: Georgia, 'Times New Roman', serif;
                                font-size: 24px;
                                line-height: 31px;
                                font-weight: 400;
                                color: #111111;
                            "
                        >
                            Informații de livrare
                        </h2>
                    </td>
                </tr>

                <tr>
                    <td
                        class="spacing"
                        style="
                            padding: 0 40px 38px;
                        "
                    >
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            style="
                                width: 100%;
                                border-top: 1px solid #111111;
                                border-bottom: 1px solid #eeeeee;
                            "
                        >
                            <tr>
                                <td
                                    class="customer-cell"
                                    width="50%"
                                    valign="top"
                                    style="
                                        width: 50%;
                                        padding: 20px 15px 20px 0;
                                    "
                                >
                                    <p
                                        style="
                                            margin: 0 0 5px;
                                            font-size: 10px;
                                            text-transform: uppercase;
                                            letter-spacing: .7px;
                                            color: #999999;
                                        "
                                    >
                                        Destinatar
                                    </p>

                                    <strong
                                        style="
                                            font-size: 14px;
                                            color: #111111;
                                        "
                                    >
                                        {recipient_name}
                                    </strong>
                                </td>

                                <td
                                    class="customer-cell"
                                    width="50%"
                                    valign="top"
                                    style="
                                        width: 50%;
                                        padding: 20px 0 20px 15px;
                                    "
                                >
                                    <p
                                        style="
                                            margin: 0 0 5px;
                                            font-size: 10px;
                                            text-transform: uppercase;
                                            letter-spacing: .7px;
                                            color: #999999;
                                        "
                                    >
                                        Telefon
                                    </p>

                                    <strong
                                        style="
                                            font-size: 14px;
                                            color: #111111;
                                        "
                                    >
                                        {phone}
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td
                                    class="customer-cell"
                                    width="50%"
                                    valign="top"
                                    style="
                                        width: 50%;
                                        padding: 20px 15px 20px 0;
                                        border-top: 1px solid #eeeeee;
                                    "
                                >
                                    <p
                                        style="
                                            margin: 0 0 5px;
                                            font-size: 10px;
                                            text-transform: uppercase;
                                            letter-spacing: .7px;
                                            color: #999999;
                                        "
                                    >
                                        E-mail
                                    </p>

                                    <span
                                        style="
                                            font-size: 14px;
                                            color: #111111;
                                            word-break: break-word;
                                        "
                                    >
                                        {email}
                                    </span>
                                </td>

                                <td
                                    class="customer-cell"
                                    width="50%"
                                    valign="top"
                                    style="
                                        width: 50%;
                                        padding: 20px 0 20px 15px;
                                        border-top: 1px solid #eeeeee;
                                    "
                                >
                                    <p
                                        style="
                                            margin: 0 0 5px;
                                            font-size: 10px;
                                            text-transform: uppercase;
                                            letter-spacing: .7px;
                                            color: #999999;
                                        "
                                    >
                                        Plata
                                    </p>

                                    <strong
                                        style="
                                            font-size: 14px;
                                            color: #111111;
                                        "
                                    >
                                        {payment}
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td
                                    colspan="2"
                                    style="
                                        padding: 20px 0;
                                        border-top: 1px solid #eeeeee;
                                    "
                                >
                                    <p
                                        style="
                                            margin: 0 0 5px;
                                            font-size: 10px;
                                            text-transform: uppercase;
                                            letter-spacing: .7px;
                                            color: #999999;
                                        "
                                    >
                                        Adresa
                                    </p>

                                    <span
                                        style="
                                            font-size: 14px;
                                            line-height: 21px;
                                            color: #111111;
                                        "
                                    >
                                        {address}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Products heading -->
                <tr>
                    <td
                        class="spacing"
                        style="
                            padding: 0 40px 14px;
                        "
                    >
                        <p
                            style="
                                margin: 0;
                                font-size: 11px;
                                line-height: 17px;
                                letter-spacing: 1px;
                                text-transform: uppercase;
                                color: #999999;
                            "
                        >
                            Comanda dumneavoastră
                        </p>

                        <h2
                            style="
                                margin: 6px 0 0;
                                font-family: Georgia, 'Times New Roman', serif;
                                font-size: 24px;
                                line-height: 31px;
                                font-weight: 400;
                                color: #111111;
                            "
                        >
                            Produse comandate
                        </h2>
                    </td>
                </tr>

                <!-- Products -->
                <tr>
                    <td
                        class="spacing"
                        style="
                            padding: 0 40px 34px;
                        "
                    >
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            style="
                                width: 100%;
                                border-top: 1px solid #111111;
                            "
                        >
                            {products}
                        </table>
                    </td>
                </tr>

                <!-- Summary -->
                <tr>
                    <td
                        class="spacing"
                        style="
                            padding: 0 40px 42px;
                        "
                    >
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            style="
                                width: 100%;
                                background-color: #f7f7f7;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 24px;
                                    "
                                >
                                    <table
                                        role="presentation"
                                        width="100%"
                                        cellspacing="0"
                                        cellpadding="0"
                                    >
                                        <tr>
                                            <td
                                                style="
                                                    padding: 0 0 12px;
                                                    font-size: 14px;
                                                    color: #777777;
                                                "
                                            >
                                                Produse
                                            </td>

                                            <td
                                                align="right"
                                                style="
                                                    padding: 0 0 12px;
                                                    font-size: 14px;
                                                    color: #111111;
                                                "
                                            >
                                                {products_total} MDL
                                            </td>
                                        </tr>

                                        {promo_row}

                                        {bonus_row}

                                        <tr>
                                            <td
                                                style="
                                                    padding: 0 0 16px;
                                                    font-size: 14px;
                                                    color: #777777;
                                                "
                                            >
                                                Livrare
                                            </td>

                                            <td
                                                align="right"
                                                style="
                                                    padding: 0 0 16px;
                                                    font-size: 14px;
                                                    color: #111111;
                                                "
                                            >
                                                {delivery_price} MDL
                                            </td>
                                        </tr>

                                        <tr>
                                            <td
                                                style="
                                                    padding-top: 17px;
                                                    border-top: 1px solid #d8d8d8;
                                                    font-size: 18px;
                                                    font-weight: 700;
                                                    color: #111111;
                                                "
                                            >
                                                Total
                                            </td>

                                            <td
                                                align="right"
                                                style="
                                                    padding-top: 17px;
                                                    border-top: 1px solid #d8d8d8;
                                                    font-size: 18px;
                                                    font-weight: 700;
                                                    color: #111111;
                                                "
                                            >
                                                {final_total} MDL
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer message -->
                <tr>
                    <td
                        align="center"
                        class="spacing"
                        style="
                            padding: 30px 40px;
                            border-top: 1px solid #eeeeee;
                            background-color: #fafafa;
                        "
                    >
                        <p
                            style="
                                margin: 0;
                                font-size: 12px;
                                line-height: 19px;
                                color: #777777;
                            "
                        >
                            Acest mesaj confirmă înregistrarea comenzii.
                            Confirmarea finală va fi oferită de operatorul Vizaje-Nica.
                        </p>
                    </td>
                </tr>

                <!-- Black footer -->
                <tr>
                    <td
                        align="center"
                        class="spacing"
                        style="
                            padding: 30px 40px;
                            background-color: #111111;
                        "
                    >
                        <p
                            style="
                                margin: 0;
                                font-family: Georgia, serif;
                                font-size: 18px;
                                letter-spacing: 2px;
                                color: #ffffff;
                            "
                        >
                            VIZAJE-NICA
                        </p>

                        <p
                            style="
                                margin: 18px 0 0;
                                font-size: 11px;
                                line-height: 18px;
                                color: #888888;
                            "
                        >
                            © Vizaje-Nica. Toate drepturile rezervate.
                            <br>
                            Acest e-mail a fost generat automat.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>