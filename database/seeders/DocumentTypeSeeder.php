<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $documentTypes = [
            'Ability Process Pre Masspro (N200)' => 'APPM', // 1
            'Agreement Special Characteristic' => 'ASC', // 2
            'Aplication Revision Drawing' => 'ARD', // 3
            'Appeareance Approval Report (AAR)' => 'AAR', // 4
            'Capability Similar Part' => 'CSP', // 5
            'Capacity Check' => 'CC', // 6
            'Capacity Production' => 'CP', // 7
            'Certified Laboratory Documentation' => 'CLD', // 8
            'Check sheet GENBA 4M N100' => 'CSG4M100', // 9
            'Checking Aids' => 'CA', // 10
            'Checksheet' => 'CS', // 11
            'Checksheet In Process' => 'CSIP', // 12
            'Consistency Confirmation Check Sheet between the Drawings and Records' => 'CCCSDR', // 13
            'Control Plan (QCPC)' => 'QCPC', // 14
            'Control Status of Material' => 'CSM', // 15
            'COQ (Certificate of Quality) / N5' => 'COQ', // 16
            'Counterpartner' => 'CPR', // 17
            'CPCPK' => 'CPCPK', // 18
            'Cs Genba 4M event N1000' => 'CSG4M1000', // 19
            'Customer Engineering Approval' => 'CEA', // 20
            'Data Properties Material L1, L2' => 'DPML', // 21
            'Data Properties Produk L1, L2' => 'DPPL', // 22
            'Data Ukur by Drawing L1, L2' => 'DUDL', // 23
            'Declaration Masspro' => 'DM', // 24
            'Design & Manufacturing Newness' => 'DMN', // 25
            'Design Record' => 'DR', // 26
            'DJTF' => 'DJTF', // 27
            'Durabilitry test delivery' => 'DTD', // 28
            'ECN Record' => 'ECNR', // 29
            'Engineering Change Request (ECR)' => 'ECR', // 30
            'Engineering Changes Documents (ECD)' => 'ECD', // 31
            'First product inspection / N1' => 'FPI', // 32
            'Floor layout Diagram' => 'FLD', // 33
            'HCSMS versi terbaru' => 'HCSMS', // 34
            'High Volume Production' => 'HVP', // 35
            'Hinanyo' => 'HNY', // 36
            'Identification List Tooling and Process' => 'ILTP', // 37
            'Initial Control Mass Production' => 'ICMP', // 38
            'Initial Part for Project' => 'IPP', // 39
            'Initial Process Studies (Ppk, Cpk )' => 'IPS', // 40
            'Initial Sample Inspection Result - ISIR' => 'ISIR', // 41
            'Inspection confirmation List' => 'ICL', // 42
            'Inspection Report' => 'IR', // 43
            'Inspection Standard' => 'IS', // 44
            'Inspection Tool' => 'IT', // 45
            'Investigation of Regular Temporary Process' => 'IRTP', // 46
            'Kakotora' => 'KKT', // 47
            'Lead Time' => 'LT', // 48
            'Limit Sample Sheet' => 'LSS', // 49
            'List of Parts Supplied by Vendor' => 'LPSV', // 50
            'Mass production check data' => 'MPCD', // 51
            'Mass Production Trial Confirmation' => 'MPTC', // 52
            'Master Sample/ Boundary Sample' => 'MSBS', // 53
            'Master Schedule' => 'MS', // 54
            'Material Confirmation' => 'MC', // 55
            'Material Data Sheet (MDS)' => 'MDS', // 56
            'Material Performace Test Summarize' => 'MPTS', // 57
            'Measurement System Analysis studies' => 'MSAS', // 58
            'Millsheet (COA) L1, L2' => 'MSCOA', // 59
            'Mold Production' => 'MP', // 60
            'MQS' => 'MQS', // 61
            'N1 NG Portion Agreement (NG Portion + N1 Attachment)' => 'NNPA', // 62
            'N200' => 'N200', // 63
            'N30/ CpCpK Special characteristic point event N100' => 'N30CPS100', // 64
            'N30/ CpCpK Special characteristic point event N1000' => 'N30CPS1000', // 65
            'N5 Event N1000' => 'N5E1000', // 66
            'N5 Lab / Verifikasi Customer' => 'N5LVC', // 67
            'N5 or N10' => 'N5N10', // 68
            'New Project Education' => 'NPE', // 69
            'OS' => 'OS', // 70
            'Other Test' => 'OT', // 71
            'Parameter setting machine' => 'PSM', // 72
            'Part History' => 'PH', // 73
            'Part Inspection Data Sheet (PIDS)' => 'PIDS', // 74
            'Part Submission Warranty (PSW)' => 'PSW', // 75
            'Past Failures and Recurrence Preventive Measures' => 'PFRPM', // 76
            'Person in Charge of QA' => 'PICQA', // 77
            'PFMEA' => 'PFMEA', // 78
            'Pokayoke System' => 'PKS', // 79
            'PPAP Submission Materials Table' => 'PSMT', // 80
            'PQCS' => 'PQCS', // 81
            'PQCS L1, L2' => 'PQL', // 82
            'PQRS' => 'PQRS', // 83
            'Production Facilities' => 'PF', // 84
            'Production Preparation Planning' => 'PPP', // 85
            'Production Process Sheet' => 'PPS', // 86
            'QA Matrix' => 'QAM', // 87
            'QAV' => 'QAV', // 88
            'QC Process Charts' => 'QCPC2', // 89 (dibedakan dari QCPC Control Plan)
            'Readiness For Masspro Trial' => 'RMT', // 90
            'Records of Compliance with Customer specific Requirements' => 'RCCSR', // 91
            'RoHS L1, L2' => 'ROHS', // 92
            'Sampel Product' => 'SP', // 93
            'Schedule training' => 'ST', // 94
            'Self Audit' => 'SA', // 95
            'SIS P' => 'SISP', // 96
            'Skill matrix + Training Man Power' => 'SMTP', // 97
            'SoC Free' => 'SOCF', // 98
            'SOP' => 'SOP', // 99
            'Spec List' => 'SPL', // 100
            'Special Inspection Plan' => 'SIP', // 101
            'Standard Packaging' => 'SPKG', // 102
            'Status ECI Implement' => 'SEI', // 103
            'Study Drawing' => 'STD', // 104
            'Supplier Development Schedule' => 'SDS', // 105
            'Supplier Evaluation Sheet' => 'SES', // 106
            'Supplier Representative Information' => 'SRI', // 107
            'Supply Chain' => 'SC', // 108
            'Surface Finishing Check' => 'SFC', // 109
            'System Check' => 'SYSC', // 110
            'Test and Durability Summarize' => 'TDS', // 111
            'Test Material' => 'TM', // 112
            'Test Product' => 'TP', // 113
            'Tooling Progress Report' => 'TPR', // 114
            'Verifikasi packing' => 'VP', // 115
            'Work Instruction' => 'WI', // 116
            'Document Hearing Before Die Go' => 'DHBDG', // 117
            'Document Hearing After Die Go' => 'DHADG', // 118
            'Berita Acara Crusher' => 'BAC', // 119
        ];

        foreach ($documentTypes as $name => $code) {
            DocumentType::updateOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }
    }
}
